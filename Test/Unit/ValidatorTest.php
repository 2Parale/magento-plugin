<?php
namespace TwoPerformant\BusinessLeagueMarketing\Test\Unit;

use PHPUnit\Framework\TestCase;
use TwoPerformant\BusinessLeagueMarketing\Model\Validator\Validator;

class ValidatorTest extends TestCase
{
    public function testValidateClickScriptUrlReturnsTrueForValidUrl()
    {
        $validator = new Validator();
        $result = $validator->validateClickScriptUrl('https://attr-2p.com/clc/1.js?param=1');
        
        $this->assertTrue($result);
    }

    public function testValidateClickScriptUrlReturnsFalseForInvalidUrl()
    {
        $validator = new Validator();
        $result = $validator->validateClickScriptUrl('invalid-url');
        
        $this->assertFalse($result);
    }

    public function testValidateClickScriptUrlReturnsFalseForInvalidDomain()
    {
        $validator = new Validator();
        $result = $validator->validateClickScriptUrl('https://foobar.com/clc/1.js');
        
        $this->assertFalse($result);
    }

    public function testValidateCickScriptReturnsFalseForNonClickScriptUrl()
    {
        $validator = new Validator();
        $result = $validator->validateClickScriptUrl('https://attr-2p.com/foobar/1.js');
        
        $this->assertFalse($result);
    }

    public function testValidateCickScriptReturnsFalseForNonHttpsUrl()
    {
        $validator = new Validator();
        $result = $validator->validateClickScriptUrl('http://attr-2p.com/clc/1.js');
        
        $this->assertFalse($result);
    }
}
