<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Core\QueryType\BuiltIn\SortSpec;

use Ibexa\Core\QueryType\BuiltIn\SortSpec\SortSpecLexerInterface;
use Ibexa\Core\QueryType\BuiltIn\SortSpec\Token;

/**
 * Dummy {@see \Ibexa\Core\QueryType\BuiltIn\SortSpec\SortSpecLexerInterface} implementation.
 */
final class SortSpecLexerStub implements SortSpecLexerInterface
{
    /** @var \Ibexa\Core\QueryType\BuiltIn\SortSpec\Token[] */
    private $tokens;

    /** @var string|null */
    private $input;

    /** @var int */
    private $position;

    public function __construct(array $tokens = [])
    {
        $this->tokens = $tokens;
        $this->position = -1;
    }

    public function consume(): Token
    {
        ++$this->position;

        return $this->tokens[$this->position];
    }

    public function isEOF(): bool
    {
        return $this->position + 1 >= count($this->tokens) - 1;
    }

    public function tokenize(string $input): void
    {
        $this->input = $input;
    }

    public function getInput(): string
    {
        return (string)$this->input;
    }

    public function peek(): ?Token
    {
        return $this->tokens[$this->position + 1] ?? null;
    }
}

class_alias(SortSpecLexerStub::class, 'eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Tests\SortSpecLexerStub');
