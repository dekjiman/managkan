<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MemberServiceTest extends TestCase
{
    public function testGeneratePublicIdReturnsString(): void
    {
        $result = generatePublicId();
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testGeneratePublicIdIsUnique(): void
    {
        $id1 = generatePublicId();
        $id2 = generatePublicId();
        $this->assertNotEquals($id1, $id2);
    }
}
