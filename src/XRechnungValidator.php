<?php

namespace XrechnungKit;

/**
 * Class for validating XML files against the XRechnung XSD schema.
 */
class XRechnungValidator
{
    private $xsdFile;

    private $errors = [];

    /**
     * Constructor to initialize the path to the XSD file.
     *
     * @param string $xsdFile The path to the XSD file.
     */
    public function __construct(string $xsdFile = '')
    {
        if (!file_exists($xsdFile)) {
            $xsdFile = dirname(__DIR__) . '/resources/schemas/XRechnungSchema.xsd';
        }
        $this->xsdFile = $xsdFile;
    }

    /**
     * Validates the provided XML file against the XSD schema.
     *
     * @param string $xmlFile The path to the XML file to validate.
     * @return bool True if valid, false otherwise.
     */
    public function validate(string $xmlFile): bool
    {
        $xml = new \DOMDocument();
        $xml->load($xmlFile);

        libxml_use_internal_errors(true);
        $isValid = $xml->schemaValidate($this->xsdFile);
        if ($isValid) {
            return true;
        }

        foreach (libxml_get_errors() as $error) {
            $this->setError($this->formatLibxmlError($error));
        }
        libxml_clear_errors();

        return false;
    }

    /**
     * Formats libxml error messages.
     *
     * @param \LibXMLError $error The libxml error object.
     * @return string The formatted error message.
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
     * Sets the errors.
     *
     * @param array $errors An array of error messages.
     * @return void
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Adds a formatted LibXML error message to the errors array.
     *
     * @param string $formatLibxmlError The formatted LibXML error message.
     * @return void
     */
    private function setError(string $formatLibxmlError)
    {
        $this->errors[] = $formatLibxmlError;
    }

    /**
     * Retrieves an array of errors.
     *
     * @return array An array of error messages.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
