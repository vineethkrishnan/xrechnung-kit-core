<?php

namespace XrechnungKit;

/**
 * Validates XRechnung XML against the bundled UBL XSD schema. Both an
 * in-memory entry point (validateContent) and a file-path entry point
 * (validate) are provided; the file-path variant is a thin convenience
 * wrapper around the in-memory one.
 */
class XRechnungValidator
{
    private string $xsdFile;

    /** @var array<int, string> */
    private array $errors = [];

    /**
     * @param string $xsdFile Optional override for the XSD file path. Defaults to the bundled XRechnungSchema.xsd.
     */
    public function __construct(string $xsdFile = '')
    {
        if (!file_exists($xsdFile)) {
            $xsdFile = dirname(__DIR__) . '/resources/schemas/XRechnungSchema.xsd';
        }
        $this->xsdFile = $xsdFile;
    }

    /**
     * In-memory validation. Loads the XML string into a DOMDocument and
     * runs schemaValidate against the configured XSD. No filesystem I/O
     * other than reading the XSD itself.
     */
    public function validateContent(string $xml): bool
    {
        $this->errors = [];

        $document = new \DOMDocument();
        $previousInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        if ($xml === '') {
            $this->errors = ['Cannot validate empty XML string'];
            libxml_use_internal_errors($previousInternalErrors);
            return false;
        }
        $parsed = $document->loadXML($xml, LIBXML_NONET);
        if (!$parsed) {
            foreach (libxml_get_errors() as $error) {
                $this->setError($this->formatLibxmlError($error));
            }
            libxml_clear_errors();
            libxml_use_internal_errors($previousInternalErrors);
            return false;
        }

        $isValid = $document->schemaValidate($this->xsdFile);
        if (!$isValid) {
            foreach (libxml_get_errors() as $error) {
                $this->setError($this->formatLibxmlError($error));
            }
        }
        libxml_clear_errors();
        libxml_use_internal_errors($previousInternalErrors);

        return $isValid;
    }

    /**
     * Convenience wrapper: read a file from disk, then validateContent.
     * Returns false if the file does not exist or cannot be read.
     */
    public function validate(string $xmlFile): bool
    {
        if (!is_file($xmlFile) || !is_readable($xmlFile)) {
            $this->errors = ["Cannot read XML file: {$xmlFile}"];
            return false;
        }
        $contents = file_get_contents($xmlFile);
        if ($contents === false) {
            $this->errors = ["Failed to read XML file: {$xmlFile}"];
            return false;
        }
        return $this->validateContent($contents);
    }

    /**
     * Formats libxml error messages.
     */
    private function formatLibxmlError(\LibXMLError $error): string
    {
        return sprintf(
            'Error %d at line %d, column %d: %s',
            $error->code,
            $error->line,
            $error->column,
            trim($error->message)
        );
    }

    /**
     * Replace the entire error set. Used by callers that want to reset state
     * between validation runs without instantiating a new validator.
     *
     * @param array<int, string> $errors An array of error messages.
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    private function setError(string $formatLibxmlError): void
    {
        $this->errors[] = $formatLibxmlError;
    }

    /**
     * @return array<int, string> The errors collected by the most recent validate / validateContent call.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
