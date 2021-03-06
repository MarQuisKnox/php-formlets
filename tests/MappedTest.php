<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 + This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once("formlets.php");
require_once("tests/FormletTest.php");

class MappedTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        $id = _fn(function($a) { return $a; });
        $id2 = _fn(function($_, $a) { return $a; });
        $pure = _pure(_val(42));
        return array
            ( array($pure->map($id))
            , array($pure->mapHTML($id2))
            , array($pure->mapBC($id, $id))
            );
    }

    public function testMappedOnce() {
        $d = new RenderDict(array("foo" => "bar"), _val(0));
        $n = NameSource::instantiate("test");
        $f = _text("foobar");

        $rdict = null;
        $rhtml = null; 

        $transformation = _fn(function ($dict, $html) use (&$rdict, &$rhtml) {
            $rdict = $dict;
            $rhtml = $html;
            return html_nop();
        });

        $f2 = $f->mapHTML($transformation);

        $i = $f2->instantiate($n);

        $r1 = $i["builder"]->build();
        $this->assertInstanceOf("RenderDict", $rdict); 
        $this->assertInstanceOf("HTMLText", $rhtml); 
        $this->assertEquals($rhtml->render(), "foobar");
        $this->assertInstanceOf("HTMLNop", $r1); 

        $r2 = $i["builder"]->buildWithDict($d);
        $this->assertInstanceOf("RenderDict", $rdict); 
        $this->assertEquals($d, $rdict); 
        $this->assertInstanceOf("HTMLText", $rhtml); 
        $this->assertEquals($rhtml->render(), "foobar");
        $this->assertInstanceOf("HTMLNop", $r2); 
    }

    public function testMappedTwice() {
        $d = new RenderDict(array("foo" => "bar"), _val(0));
        $n = NameSource::instantiate("test");
        $f = _text("foobar");

        $rdict1 = null;
        $rhtml1 = null; 
        $rdict2 = null;
        $rhtml2 = null; 

        $transformation = _fn(function ($dict, $html) use (&$rdict1, &$rhtml1) {
            $rdict1 = $dict;
            $rhtml1 = $html;
            return html_nop();
        });

        $transformation2 = _fn(function ($dict, $html) use (&$rdict2, &$rhtml2) {
            $rdict2 = $dict;
            $rhtml2 = $html;
            return html_text("baz");
        });

        $f2 = $f->mapHTML($transformation)->mapHTML($transformation2);

        $i = $f2->instantiate($n);

        $r1 = $i["builder"]->build();
        $this->assertInstanceOf("RenderDict", $rdict1); 
        $this->assertInstanceOf("RenderDict", $rdict2); 
        $this->assertInstanceOf("HTMLText", $rhtml1); 
        $this->assertInstanceOf("HTMLNop", $rhtml2); 
        $this->assertEquals($rhtml1->render(), "foobar");
        $this->assertEquals($rhtml2->render(), "");
        $this->assertInstanceOf("HTMLText", $r1); 
        $this->assertEquals($r1->render(), "baz"); 

        $r2 = $i["builder"]->buildWithDict($d);
        $this->assertInstanceOf("RenderDict", $rdict1); 
        $this->assertInstanceOf("RenderDict", $rdict2); 
        $this->assertEquals($d, $rdict1); 
        $this->assertEquals($d, $rdict2); 
        $this->assertInstanceOf("HTMLText", $rhtml1); 
        $this->assertInstanceOf("HTMLNop", $rhtml2); 
        $this->assertEquals($rhtml1->render(), "foobar");
        $this->assertEquals($rhtml2->render(), "");
        $this->assertInstanceOf("HTMLText", $r2);
        $this->assertEquals($r2->render(), "baz");
    }
}

?>
