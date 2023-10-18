<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php


class HikashopExpressionInc {

    var $suppress_errors = false;
    var $last_error = null;

    var $v = array('e'=>2.71,'pi'=>3.14); // variables (and constants)
    var $f = array(); // user-defined functions
    var $vb = array('e', 'pi'); // constants
    var $fb = array(  // built-in functions
        'sin','sinh','arcsin','asin','arcsinh','asinh',
        'cos','cosh','arccos','acos','arccosh','acosh',
        'tan','tanh','arctan','atan','arctanh','atanh',
        'sqrt','abs','ln','log', 'ceil', 'intval', 'floor', 'round', 'exp');

    var $functions = array(); // function defined outside of Expression as closures

    function __construct() {
        $this->v['pi'] = pi();
        $this->v['e'] = exp(1);
    }

    function e($expr) {
        return $this->evaluate($expr);
    }

    function evaluate($expr) {
        $this->last_error = null;
        $expr = trim($expr);
        if (substr($expr, -1, 1) == ';') $expr = substr($expr, 0, strlen($expr)-1); // strip semicolons at the end
        if (preg_match('/^\s*([a-z]\w*)\s*=(?!~|=)\s*(.+)$/', $expr, $matches)) {
            if (in_array($matches[1], $this->vb)) { // make sure we're not assigning to a constant
                return $this->trigger("cannot assign to constant '$matches[1]'");
            }
            $tmp = $this->pfx($this->nfx($matches[2]));
            $this->v[$matches[1]] = $tmp; // if so, stick it in the variable array
            return $this->v[$matches[1]]; // and return the resulting value
        } elseif (preg_match('/^\s*([a-z]\w*)\s*\((?:\s*([a-z]\w*(?:\s*,\s*[a-z]\w*)*)\s*)?\)\s*=(?!~|=)\s*(.+)$/', $expr, $matches)) {
            $fnn = $matches[1]; // get the function name
            if (in_array($matches[1], $this->fb)) { // make sure it isn't built in
                return $this->trigger("cannot redefine built-in function '$matches[1]()'");
            }

            if ($matches[2] != "") {
                $args = explode(",", preg_replace("/\s+/", "", $matches[2])); // get the arguments
            } else {
                $args = array();
            }
            if (($stack = $this->nfx($matches[3])) === false) return false; // see if it can be converted to postfix
            for ($i = 0; $i<count($stack); $i++) { // freeze the state of the non-argument variables
                $token = $stack[$i];
                if (preg_match('/^[a-z]\w*$/', $token) and !in_array($token, $args)) {
                    if (array_key_exists($token, $this->v)) {
                        $stack[$i] = $this->v[$token];
                    } else {
                        return $this->trigger("undefined variable '$token' in function definition");
                    }
                }
            }
            $this->f[$fnn] = array('args'=>$args, 'func'=>$stack);
            return true;
        } else {
            return $this->pfx($this->nfx($expr)); // straight up evaluation, woo
        }
    }

    function vars() {
        $output = $this->v;
        unset($output['pi']);
        unset($output['e']);
        return $output;
    }

    function funcs() {
        $output = array();
        foreach ($this->f as $fnn=>$dat)
            $output[] = $fnn . '(' . implode(',', $dat['args']) . ')';
        return $output;
    }


    function nfx($expr) {
        $index = 0;
        $stack = new HikashopExpressionStack;
        $output = array(); // postfix form of expression, to be passed to pfx()
        $expr = trim($expr);

        $ops   = array('+', '-', '*', '/', '^', '_', '%', '>', '<', '>=', '<=', '==', '!=', '=~', '&&', '||', '!');
        $ops_r = array('+'=>0,'-'=>0,'*'=>0,'/'=>0,'%'=>0,'^'=>1,'>'=>0,
                       '<'=>0,'>='=>0,'<='=>0,'=='=>0,'!='=>0,'=~'=>0,
                       '&&'=>0,'||'=>0,'!'=>0); // right-associative operator?
        $ops_p = array('+'=>3,'-'=>3,'*'=>4,'/'=>4,'_'=>4,'%'=>4,'^'=>5,'>'=>2,'<'=>2,
                       '>='=>2,'<='=>2,'=='=>2,'!='=>2,'=~'=>2,'&&'=>1,'||'=>1,'!'=>5); // operator precedence

        $expecting_op = false; // we use this in syntax-checking the expression

        $first_argument = false;
        $i = 0;
        $matcher = false;
        while(1) { // 1 Infinite Loop ;)
            $op = substr(substr($expr, $index), 0, 2); // get the first two characters at the current index
            if (preg_match("/^[+\-*\/^_\"<>=%(){\[!~,](?!=|~)/", $op) || preg_match("/\w/", $op)) {
                $op = substr($expr, $index, 1);
            }
            $single_str = '(?<!\\\\)"(?:(?:(?<!\\\\)(?:\\\\{2})*\\\\)"|[^"])*(?<![^\\\\]\\\\)"';
            $double_str = "(?<!\\\\)'(?:(?:(?<!\\\\)(?:\\\\{2})*\\\\)'|[^'])*(?<![^\\\\]\\\\)'";
            $regex = "(?<!\\\\)\/(?:[^\/]|\\\\\/)+\/[imsxUXJ]*";
            $json = '[\[{](?>"(?:[^"]|\\\\")*"|[^[{\]}]|(?1))*[\]}]';
            $number = '[\d.]+e\d+|\d+(?:\.\d*)?|\.\d+';
            $name = '[a-z]\w*\(?|\\$\w+';
            $parenthesis = '\\(';
            $ex = preg_match("%^($single_str|$double_str|$json|$name|$regex|$number|$parenthesis)%", substr($expr, $index), $match);
            if ($op == '[' && $expecting_op && $ex) {
                if (!preg_match("/^\[(.*)\]$/", $match[1], $matches)) {
                    return $this->trigger("invalid array access");
                }
                $stack->push('[');
                $stack->push($matches[1]);
                $index += strlen($match[1]);
            } elseif ($op == '-' and !$expecting_op) { // is it a negation instead of a minus?
                $stack->push('_'); // put a negation on the stack
                $index++;
            } elseif ($op == '_') { // we have to explicitly deny this, because it's legal on the stack
                return $this->trigger("illegal character '_'"); // but not in the input expression
            } elseif ($ex && $matcher && preg_match("%^" . $regex . "$%", $match[1])) {
                $stack->push('"' . $match[1] . '"');
                $index += strlen($match[1]);
                $op = null;
                $expecting_op = false;
                $matcher = false;
                break;
            } elseif (((in_array($op, $ops) or $ex) and $expecting_op) or in_array($op, $ops) and !$expecting_op or
                      (!$matcher && $ex && preg_match("%^" . $regex . "$%", $match[1]))) {
                while($stack->count > 0 and ($o2 = $stack->last()) and in_array($o2, $ops) and ($ops_r[$op] ? $ops_p[$op] < $ops_p[$o2] : $ops_p[$op] <= $ops_p[$o2])) {
                    $output[] = $stack->pop(); // pop stuff off the stack into the output

                }
                $stack->push($op); // finally put OUR operator onto the stack
                $index += strlen($op);
                $expecting_op = false;
                $matcher = $op == '=~';
            } elseif ($op == ')' and $expecting_op || !$ex) { // ready to close a parenthesis?
                while (($o2 = $stack->pop()) != '(') { // pop off the stack back to the last (
                    if (is_null($o2)) return $this->trigger("unexpected ')'");
                    else $output[] = $o2;
                }
                if (preg_match("/^([a-z]\w*)\($/", $stack->last(2), $matches)) { // did we just close a function?
                    $fnn = $matches[1]; // get the function name
                    $arg_count = $stack->pop(); // see how many arguments there were (cleverly stored on the stack, thank you)
                    $output[] = $stack->pop(); // pop the function and push onto the output
                    if (in_array($fnn, $this->fb)) { // check the argument count
                        if($arg_count > 1)
                            return $this->trigger("too many arguments ($arg_count given, 1 expected)");
                    } elseif (array_key_exists($fnn, $this->f)) {
                        if ($arg_count != count($this->f[$fnn]['args']))
                            return $this->trigger("wrong number of arguments ($arg_count given, " . count($this->f[$fnn]['args']) . " expected) " . json_encode($this->f[$fnn]['args']));
                    } elseif (array_key_exists($fnn, $this->functions)) {
                        $func_reflection = new ReflectionFunction($this->functions[$fnn]);
                        $count = $func_reflection->getNumberOfParameters();
                        if ($arg_count != $count)
                            return $this->trigger("wrong number of arguments ($arg_count given, " . $count . " expected)");
                    } else { // did we somehow push a non-function on the stack? this should never happen
                        return $this->trigger("internal error");
                    }
                }
                $index++;
            } elseif ($op == ',' and $expecting_op) { // did we just finish a function argument?

                while (($o2 = $stack->pop()) != '(') {
                    if (is_null($o2)) return $this->trigger("unexpected ','"); // oops, never had a (
                    else $output[] = $o2; // pop the argument expression stuff and push onto the output
                }
                if (!preg_match("/^([a-z]\w*)\($/", $stack->last(2), $matches))
                    return $this->trigger("unexpected ','");
                if ($first_argument) {
                    $first_argument = false;
                } else {
                    $stack->push($stack->pop()+1); // increment the argument count
                }
                $stack->push('('); // put the ( back on, we'll need to pop back to it again
                $index++;
                $expecting_op = false;
            } elseif ($op == '(' and !$expecting_op) {
                $stack->push('('); // that was easy
                $index++;
                $allow_neg = true;
            } elseif ($ex and !$expecting_op) { // do we now have a function/variable/number?
                $expecting_op = true;
                $val = $match[1];
                if ($op == '[' || $op == "{" || preg_match("/null|true|false/", $match[1])) {
                    $output[] = $val;
                } elseif (preg_match("/^([a-z]\w*)\($/", $val, $matches)) { // may be func, or variable w/ implicit multiplication against parentheses...
                    if (in_array($matches[1], $this->fb) or
                        array_key_exists($matches[1], $this->f) or
                        array_key_exists($matches[1], $this->functions)) { // it's a func
                        $stack->push($val);
                        $stack->push(0);
                        $stack->push('(');
                        $expecting_op = false;
                    } else { // it's a var w/ implicit multiplication
                        $val = $matches[1];
                        $output[] = $val;
                    }
                } else { // it's a plain old var or num
                    $output[] = $val;
                    if (preg_match("/^([a-z]\w*)\($/", $stack->last(3))) {
                        $first_argument = true;
                        while (($o2 = $stack->pop()) != '(') {
                            if (is_null($o2)) return $this->trigger("unexpected error"); // oops, never had a (
                            else $output[] = $o2; // pop the argument expression stuff and push onto the output
                        }
                        if (!preg_match("/^([a-z]\w*)\($/", $stack->last(2), $matches))
                            return $this->trigger("unexpected error");

                        $stack->push($stack->pop()+1); // increment the argument count
                        $stack->push('('); // put the ( back on, we'll need to pop back to it again
                    }
                }
                $index += strlen($val);
            } elseif ($op == ')') { // miscellaneous error checking
                return $this->trigger("unexpected ')'");
            } elseif (in_array($op, $ops) and !$expecting_op) {
                return $this->trigger("unexpected operator '$op'");
            } else { // I don't even want to know what you did to get here
                return $this->trigger("an unexpected error occured " . json_encode($op) . " " . json_encode($match) . " ". ($ex?'true':'false') . " " . $expr);
            }
            if ($index == strlen($expr)) {
                if (in_array($op, $ops)) { // did we end with an operator? bad.
                    return $this->trigger("operator '$op' lacks operand");
                } else {
                    break;
                }
            }
            while (substr($expr, $index, 1) == ' ') { // step the index past whitespace (pretty much turns whitespace
                $index++;                             // into implicit multiplication if no operator is there)
            }

        }
        while (!is_null($op = $stack->pop())) { // pop everything off the stack and push onto output
            if ($op == '(') return $this->trigger("expecting ')'"); // if there are (s on the stack, ()s were unbalanced
            $output[] = $op;
        }
        return $output;
    }

    function pfx($tokens, $vars = array()) {

        if ($tokens == false) return false;
        $stack = new HikashopExpressionStack();
        foreach ($tokens as $token) { // nice and easy
            if (in_array($token, array('+', '-', '*', '/', '^', '<', '>', '<=', '>=', '==', '&&', '||', '!=', '=~', '%'))) {
                $op2 = $stack->pop();
                $op1 = $stack->pop();
                switch ($token) {
                    case '+':
                        if (is_string($op1) || is_string($op2)) {
                            $stack->push((string)$op1 . (string)$op2);
                        } else {
                            $stack->push($op1 + $op2);
                        }
                        break;
                    case '-':
                        $stack->push($op1 - $op2); break;
                    case '*':
                        $stack->push($op1 * $op2); break;
                    case '/':
                        if ($op2 == 0) return $this->trigger("division by zero");
                        $stack->push($op1 / $op2); break;
                    case '%':
                        $stack->push($op1 % $op2); break;
                    case '^':
                        $stack->push(pow($op1, $op2)); break;
                    case '>':
                        $stack->push($op1 > $op2); break;
                    case '<':
                        $stack->push($op1 < $op2); break;
                    case '>=':
                        $stack->push($op1 >= $op2); break;
                    case '<=':
                        $stack->push($op1 <= $op2); break;
                    case '==':
                        if (is_array($op1) && is_array($op2)) {
                            $stack->push(json_encode($op1) == json_encode($op2));
                        } else {
                            $stack->push($op1 == $op2);
                        }
                        break;
                    case '!=':
                        if (is_array($op1) && is_array($op2)) {
                            $stack->push(json_encode($op1) != json_encode($op2));
                        } else {
                            $stack->push($op1 != $op2);
                        }
                        break;
                    case '=~':
                        $value = @preg_match($op2, $op1, $match);

                        if (!is_int($value)) {
                            return $this->trigger("Invalid regex " . json_encode($op2));
                        }
                        $stack->push($value);
                        for ($i = 0; $i < count($match); $i++) {
                            $this->v['$' . $i] = $match[$i];
                        }
                        break;
                    case '&&':
                        $stack->push($op1 ? $op2 : $op1); break;
                    case '||':
                        $stack->push($op1 ? $op1 : $op2); break;
                }
            } elseif ($token == '!') {
                $stack->push(!$stack->pop());
            } elseif ($token == '[') {
                $selector = $stack->pop();
                $object = $stack->pop();
                if (is_object($object)) {
                    $stack->push($object->$selector);
                } elseif (is_array($object)) {
                    $stack->push($object[$selector]);
                } else {
                    return $this->trigger("invalid object for selector");
                }
            } elseif ($token == "_") {
                $stack->push(-1*$stack->pop());
            } elseif (preg_match("/^([a-z]\w*)\($/", $token, $matches)) { // it's a function!
                $fnn = $matches[1];
                if (in_array($fnn, $this->fb)) { // built-in function:
                    if (is_null($op1 = $stack->pop())) return $this->trigger("internal error");
                    $fnn = preg_replace("/^arc/", "a", $fnn); // for the 'arc' trig synonyms
                    if ($fnn == 'ln') $fnn = 'log';
                    $stack->push($fnn($op1)); // perfectly safe variable function call
                } elseif (array_key_exists($fnn, $this->f)) { // user function
                    $args = array();
                    for ($i = count($this->f[$fnn]['args'])-1; $i >= 0; $i--) {
                        if ($stack->empty()) {
                            return $this->trigger("internal error " . $fnn . " " . json_encode($this->f[$fnn]['args']));
                        }
                        $args[$this->f[$fnn]['args'][$i]] = $stack->pop();
                    }
                    $stack->push($this->pfx($this->f[$fnn]['func'], $args)); // yay... recursion!!!!
                } else if (array_key_exists($fnn, $this->functions)) {
                    $reflection = new ReflectionFunction($this->functions[$fnn]);
                    $count = $reflection->getNumberOfParameters();
                    for ($i = $count-1; $i >= 0; $i--) {
                        if ($stack->empty()) {
                            return $this->trigger("internal error");
                        }
                        $args[] = $stack->pop();
                    }
                    $stack->push($reflection->invokeArgs($args));
                }
            } else {
                if (preg_match('/^([\[{](?>"(?:[^"]|\\")*"|[^[{\]}]|(?1))*[\]}])$/', $token) ||
                    preg_match("/^(null|true|false)$/", $token)) { // json
                    if ($token == 'null') {
                        $value = null;
                    } elseif ($token == 'true') {
                        $value = true;
                    } elseif ($token == 'false') {
                        $value = false;
                    } else {
                        $value = json_decode($token);
                        if ($value == null) {
                            return $this->trigger("invalid json " . $token);
                        }
                    }
                    $stack->push($value);
                } elseif (is_numeric($token)) {
                    $stack->push(0+$token);
                } else if (preg_match("/^['\\\"](.*)['\\\"]$/", $token)) {
                    $stack->push(json_decode(preg_replace_callback("/^['\\\"](.*)['\\\"]$/", function($matches) {
                        $m = array("/\\\\'/", '/(?<!\\\\)"/');
                        $r = array("'", '\\"');
                        return '"' . preg_replace($m, $r, $matches[1]) . '"';
                    }, $token)));
                } elseif (array_key_exists($token, $this->v)) {
                    $stack->push($this->v[$token]);
                } elseif (array_key_exists($token, $vars)) {
                    $stack->push($vars[$token]);
                } else {
                    return $this->trigger("undefined variable '$token'");
                }
            }
        }
        if ($stack->count != 1) return $this->trigger("internal error");
        return $stack->pop();
    }

    function trigger($msg) {
        $this->last_error = $msg;
        if (!$this->suppress_errors) trigger_error($msg, E_USER_WARNING);
        return false;
    }
}

class HikashopExpressionStack {

    var $stack = array();
    var $count = 0;

    function push($val) {
        $this->stack[$this->count] = $val;
        $this->count++;
    }

    function pop() {
        if ($this->count > 0) {
            $this->count--;
            return $this->stack[$this->count];
        }
        return null;
    }

    function empty() {
        return empty($this->stack);
    }

    function last($n=1) {
        if (isset($this->stack[$this->count-$n])) {
          return $this->stack[$this->count-$n];
        }
        return;
    }
}
