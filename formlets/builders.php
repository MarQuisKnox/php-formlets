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

require_once("checking.php");
require_once("values.php");
require_once("html.php");

/******************************************************************************
 * Turn a value to two dictionaries:
 *  - one contains the original values as inputted by the user.
 *  - one contains the origins and the errors on those values.
 */
class RenderDict {
    private $_values; // array
    private $_errors; // array
    private $_empty; // bool 

    public function isEmpty() {
        return $this->_empty;
    }

    public function value($name) {
        if ($this->valueExists($name))
            return $this->_values[$name];
        return null;
    }

    public function valueExists($name) {
        return array_key_exists($name, $this->_values);
    }

    public function errors($name) {
        if (array_key_exists($name, $this->_errors))
            return $this->_errors[$name];
        return null;
    }

    public function __construct($inp, Value $value, $_empty = false) {
        guardIsBool($_empty);
        $this->_values = $inp; 
        $value = $value->force();
        if ($value instanceof ErrorValue) {
            $this->_errors = $value->toDict();
        }
        else {
            $this->_errors = array(); 
        }
        $this->_empty = $_empty;
    }

    private static $_emptyInst = null;

    public static function _empty() {
        // ToDo: Why does this not work?
        /*if (self::_emptyInst === null) {
            self::_emptyInst = new RenderDict(_val(0));
        }
        return self::_emptyInst;*/
        return new RenderDict(array(), _val(0), true);
    }  
}

/******************************************************************************
 * Fairly simple implementation of a Builder. Can render strings and supports
 * combining of builders. A more sophisticated version could be build upon
 * HTML primitives.
 */

abstract class Builder {
    /* Returns HTML. */
    abstract public function buildWithDict(RenderDict $dict);
    public function build() {
        return $this->buildWithDict(RenderDict::_empty());
    }

    /**
     * Map a transformation over the result of the Builder. The transformation
     * gets the used RenderDict and the HTML result of the Builder and should
     * return a new HTML.
     */
    public function map(FunctionValue $transformation) {
        return new MappedBuilder($this, $transformation);
    } 
}

/* Builder that combines two sub builders by adding the output of the 
 * builders.
 */
class CombinedBuilder extends Builder {
    private $_l; // Builder
    private $_r; // Builder

    public function __construct(Builder $left, Builder $right) {
        $this->_l = $left;
        $this->_r = $right;
    }

    public function buildWithDict(RenderDict $dict) {
        return html_concat( $this->_l->buildWithDict($dict)
                          , $this->_r->buildWithDict($dict)
                          );
    }
}

/* Builder where a function is mapped over the result of another builder. */
class MappedBuilder extends Builder {
    private $_builder; // Builder
    private $_transformation; // FunctionValue 

    public function __construct(Builder $builder, FunctionValue $transformation) {
        guardHasArity($transformation, 2);
        $this->_builder = $builder;
        $this->_transformation = $transformation;
    }

    public function buildWithDict(RenderDict $dict) {
        $base = $this->_builder->buildWithDict($dict);
        $res = $this->_transformation
                ->apply(_val($dict))
                ->apply(_val($base))
                ->get();
        guardIsHTML($res);
        return $res;
    }
}

/* A builder that produces a completely empty piece of HTML. */
class NopBuilder extends Builder {
    public function buildWithDict(RenderDict $dict) {
        return html_nop();
    }
}

/* A builder that produces a constant output. */
class TextBuilder extends Builder {
    private $_content; // string

    public function __construct($content) {
        $this->_content = html_text($content);
    }

    public function buildWithDict(RenderDict $dict) {
        return $this->_content;
    }
}

/**
 * Interface to be implemented by classes that should be used by TagBuilder. 
 */
interface TagBuilderCallbacks {
    /**
     * Get the attributes for the tag to be build. Should return a dict of
     * string => string.
     */
    public function getAttributes(RenderDict $dict, $name);

    /**
     * Get the content of the new tag. Should return an HTML or null.
     */
    public function getContent(RenderDict $dict, $name);
}

/* Builds a simple html tag. */
class TagBuilder extends Builder {
    private $_tag_name; // string
    private $_callback_object; // object
    private $_name; // string

    public function __construct( $tag_name, TagBuilderCallbacks $callback_object, $name = null) {  
        guardIsString($tag_name);
        guardIfNotNull($name, "guardIsString");
        $this->_tag_name = $tag_name;
        $this->_callback_object = $callback_object;
        $this->_name = $name;
    }

    public function buildWithDict(RenderDict $dict) {
        $attributes = $this->_callback_object->getAttributes($dict, $this->_name);
        $content = $this->_callback_object->getContent($dict, $this->_name);
        return html_tag($this->_tag_name, $attributes, $content); 
    }
}
    
?>
