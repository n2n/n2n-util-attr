<?php
namespace n2n\util\type\attrs;

use PHPUnit\Framework\TestCase;
use n2n\util\type\mock\StringBackedEnumMock;
use n2n\util\type\mock\PureEnumMock;
use n2n\util\StringUtils;

class DataMapTest extends TestCase {
	
	function testSet() {
		$dataMap = new DataMap([ 'key1' => ['skey1' => 'string']]);
		
		$dataMap->set('key1/skey1/sskey1', 'huii');
		
		$array = $dataMap->toArray();
		
		$this->assertTrue($array['key1']['skey1']['sskey1'] === 'huii');
	}

	function testClean() {
		$dataMap = new DataMap([ 'key1' => ['skey1' => " \t st\x00ri\r\nng \r\n \n"]]);

		$dataMap->cleanStrings(['key1/skey1']);

		$this->assertEquals("stri ng", $dataMap->reqString('key1/skey1'));
	}

	function testClean2() {
		$dataMap = new DataMap([ 'key1' => ['skey1' => " \t st\x00ri\r\nng \r\n \n"]]);

		$dataMap->cleanStrings(['key1/skey1'], false);

		$this->assertEquals(" \t stri\r\nng \r\n \n", $dataMap->reqString('key1/skey1'));
	}

	function testEnum() {
		$dataMap = new DataMap(['key1' => 'value-1', 'key2' => 'CASE2']);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataMap->reqEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataMap->reqEnum('key2', PureEnumMock::cases()));
	}
}