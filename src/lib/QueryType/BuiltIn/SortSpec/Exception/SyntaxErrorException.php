<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\QueryType\BuiltIn\SortSpec\Exception;

use Ibexa\Core\QueryType\BuiltIn\SortSpec\Token;
use RuntimeException;

final class SyntaxErrorException extends RuntimeException
{
    public static function fromUnexpectedToken(string $input, Token $token, array $expectedTypes): self
    {
        $message = sprintf(
            'Error while parsing sorting specification: "%s": Unexpected token %s (%s) at position %d. Expected one of the following tokens: %s',
            $input,
            $token->getValue(),
            $token->getType(),
            $token->getPosition(),
            implode(' ', $expectedTypes)
        );

        return new self($message);
    }
}

class_alias(SyntaxErrorException::class, 'eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Exception\SyntaxErrorException');
