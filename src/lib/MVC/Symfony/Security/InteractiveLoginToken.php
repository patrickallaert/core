<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\MVC\Symfony\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * This token is used when a user has been matched by a foreign user provider.
 * It is injected in SecurityContext to replace the original token as this one holds a new user.
 */
class InteractiveLoginToken extends UsernamePasswordToken
{
    /** @var string */
    private $originalTokenType;

    public function __construct(UserInterface $user, $originalTokenType, $credentials, $providerKey, array $roles = [])
    {
        parent::__construct($user, $credentials, $providerKey, $roles);
        $this->originalTokenType = $originalTokenType;
    }

    /**
     * @return string
     */
    public function getOriginalTokenType()
    {
        return $this->originalTokenType;
    }

    public function __serialize(): array
    {
        return [$this->originalTokenType, parent::__serialize()];
    }

    public function __unserialize($serialized): void
    {
        [$this->originalTokenType, $parentStr] = $serialized;
        parent::__unserialize($parentStr);
    }
}

class_alias(InteractiveLoginToken::class, 'eZ\Publish\Core\MVC\Symfony\Security\InteractiveLoginToken');
