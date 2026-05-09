<?php

declare(strict_types=1);

namespace XrechnungKit\Exception;

/**
 * Root of the kit's exception hierarchy. Per architecture section 11 the kit
 * surfaces three exception families, all extending this base: Mapping
 * DataException (thrown at value-object construction), GenerationException
 * (thrown by the Generator), and ValidationException (thrown only when
 * validateOrThrow is used).
 */
class XRechnungKitException extends \RuntimeException
{
}
