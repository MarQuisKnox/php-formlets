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

trait FormletTestTrait {
    protected function instantiateFormlet($formlet) {
        return $formlet->instantiate(NameSource::instantiate("test"));
    }

    /**
     * Thing has correct class.
     * @dataProvider formlets
     */
    public function testHasFormletClass($formlet) {
        $this->assertInstanceOf("Formlet", $formlet);
    }
     
    /**
     * Builder has correct class.
     * @dataProvider formlets
     */
    public function testBuilderHasBuilderClass($formlet) { 
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("Builder", $res["builder"]);
    }

    /**
     * Collector has correct class.
     * @dataProvider formlets
     */
    public function testCollectorHasCollectorClass($formlet) { 
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("Collector", $res["collector"]);
    }

    /**
     * Name source has correct class.
     * @dataProvider formlets
     */
    public function testNameSourceHasNameSourceClass($formlet) {
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("NameSource", $res["name_source"]);
    }
}

?>
