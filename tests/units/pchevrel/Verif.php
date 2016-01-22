<?php
namespace tests\units\pchevrel;

use atoum;
use pchevrel\Verif as _Verif;

require_once __DIR__ . '/../bootstrap.php';

class Verif extends atoum\test
{
    public function setProtocolDP()
    {
        return [
            ['http', 'http://'],
        ];
    }

    /**
     * @dataProvider setProtocolDP
     */
    public function testSetProtocol($a, $b)
    {
        $obj = new _Verif('title');
        $obj->setProtocol($a);
        $this
            ->string($obj->protocol)
                ->isEqualTo($b);
    }

    public function setURIDP()
    {
        return [
            ['http', 'test.org', 'api/', 'foo/bar/baz', 'http://test.org/api/foo/bar/baz'],
        ];
    }

    /**
     * @dataProvider setURIDP
     */
    public function testSetURI($a, $b, $c, $d, $e)
    {
        $obj = new _Verif('title');
        $obj->setProtocol($a);
        $obj->setHost($b);
        $obj->setPathPrefix($c);
        $obj->setPath($d);
        $this
            ->string($obj->uri)
                ->isEqualTo($e);
    }

    public function isJSONDP()
    {
        return [
            ['valid.json', true],
            ['not_valid.json', false],
        ];
    }

    /**
     * @dataProvider isJSONDP
     */
    public function testIsJSON($a, $b)
    {
        $obj = new _Verif('title');
        $obj->content = file_get_contents(TEST_FILES . '/JSON/' . $a);
        $obj->isJSON();

        $this
            ->integer($obj->test_count)
                ->isEqualTo(1);

        $this
            ->boolean(empty($obj->errors))
                ->isEqualTo($b);
    }

    public function isNumericDP()
    {
        return [
            [42, true],
            [42.2, true],
            [-1, true],
            [0, true],
            ['12', true],
            ['{"12"}', false],
            ["12 ", false],
        ];
    }

    /**
     * @dataProvider isNumericDP
     */
    public function testIsNumeric($a, $b)
    {
        $obj = new _Verif('title');
        $obj->content = $a;
        $obj->isNumeric();

        $this
            ->integer($obj->test_count)
                ->isEqualTo(1);

        $this
            ->boolean(empty($obj->errors))
                ->isEqualTo($b);
    }

    public function hasKeyDP()
    {
        return [
            ['anObject', true],
            ['numericProperty', false],
            ['Brah', false],
        ];
    }

    /**
     * @dataProvider hasKeyDP
     */
    public function testHasKey($a, $b)
    {
        $obj = new _Verif('title');
        $obj->content = file_get_contents(TEST_FILES . '/JSON/valid.json');
        $obj->hasKey($a);

        $this
            ->integer($obj->test_count)
                ->isEqualTo(1);

        $this
            ->boolean(empty($obj->errors))
                ->isEqualTo($b);
    }

    public function hasKeysDP()
    {
        return [
            [['anObject', 'arrayOfObjects'], true],
            [['anObject', 'nope'], false],
            [['anObject'], true],
        ];
    }

    /**
     * @dataProvider hasKeysDP
     */
    public function testHasKeys($a, $b)
    {
        $obj = new _Verif('title');
        $obj->content = file_get_contents(TEST_FILES . '/JSON/valid.json');
        $obj->hasKeys($a);

        $this
            ->boolean(empty($obj->errors))
                ->isEqualTo($b);
    }

    public function isEqualToDP()
    {
        return [
            ['test', 'test', true],
            ['test', 'test2', false],
            ['-12', '12', false],
        ];
    }

    /**
     * @dataProvider isEqualToDP
     */
    public function testIsEqualTo($a, $b, $c)
    {
        $obj = new _Verif('title');
        $obj->content = $b;
        $obj->isEqualTo($a);

        $this
            ->integer($obj->test_count)
                ->isEqualTo(1);

        $this
            ->boolean(empty($obj->errors))
                ->isEqualTo($c);
    }

    public function containsDP()
    {
        return [
            ['2011-09-23', true],
            ['test', false],
        ];
    }

    /**
     * @dataProvider containsDP
     */
    public function testContains($a, $b)
    {
        $obj = new _Verif('title');
        $obj->content = file_get_contents(TEST_FILES . '/JSON/valid.json');
        $obj->contains($a);

        $this
            ->integer($obj->test_count)
                ->isEqualTo(1);

        $this
            ->boolean(empty($obj->errors))
                ->isEqualTo($b);
    }

    public function reportDP()
    {
        return [
            [42, 'succes.php'],
            ['nope', 'errors.php'],
        ];
    }

    /**
     * @dataProvider reportDP
     */
    public function testReport($a, $b)
    {
        $obj = new _Verif('title');
        $obj->content = $a;
        $obj->isEqualTo(42);
        $obj->isNumeric();
        ob_start();
        $obj->report();
        ob_end_clean();

        include TEST_FILES . '/reports/' . $b;

        $this
            ->string($obj->report_output)
                ->isEqualTo($report);
    }

    public function colorizeOutputDP()
    {
        return [
            ['test', 'green',   true,  "\033[1;37m\033[42mtest\033[0m"],
            ['test', 'green',   false, "\033[32mtest\033[0m"],
            ['test', 'yellow',  true,  "\033[1;37m\033[43mtest\033[0m"],
            ['test', 'yellow',  false, "\033[33mtest\033[0m"],
            ['test', 'red',     true,  "\033[1;37m\033[41mtest\033[0m"],
            ['test', 'red',     false, "\033[31mtest\033[0m"],
            ['test', 'blue',    true,  "\033[1;37m\033[44mtest\033[0m"],
            ['test', 'blue',    false, "\033[1;34mtest\033[0m"],
            ['test', 'unknown', true,  "test\033[0m"],
        ];
    }

    /**
     * @dataProvider colorizeOutputDP
     */
    public function testColorizeOutput($a, $b, $c, $d)
    {
        $this
            ->string(_Verif::colorizeOutput($a, $b, $c))
                ->isEqualTo($d);
    }
}
