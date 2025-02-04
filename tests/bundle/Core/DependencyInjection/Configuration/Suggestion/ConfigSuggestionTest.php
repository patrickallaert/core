<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Core\DependencyInjection\Configuration\Suggestion;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use PHPUnit\Framework\TestCase;

class ConfigSuggestionTest extends TestCase
{
    public function testEmptyConstructor()
    {
        $suggestion = new ConfigSuggestion();
        $this->assertNull($suggestion->getMessage());
        $this->assertSame([], $suggestion->getSuggestion());
        $this->assertFalse($suggestion->isMandatory());
    }

    public function testConfigSuggestion()
    {
        $message = 'some message';
        $configArray = ['foo' => 'bar'];

        $suggestion = new ConfigSuggestion($message, $configArray);
        $this->assertSame($message, $suggestion->getMessage());
        $this->assertSame($configArray, $suggestion->getSuggestion());
        $this->assertFalse($suggestion->isMandatory());

        $newMessage = 'foo bar';
        $suggestion->setMessage($newMessage);
        $this->assertSame($newMessage, $suggestion->getMessage());

        $newConfigArray = ['ibexa' => 'publish'];
        $suggestion->setSuggestion($newConfigArray);
        $this->assertSame($newConfigArray, $suggestion->getSuggestion());

        $suggestion->setMandatory(true);
        $this->assertTrue($suggestion->isMandatory());
    }
}

class_alias(ConfigSuggestionTest::class, 'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Tests\ConfigSuggestionTest');
