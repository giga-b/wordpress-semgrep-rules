<?php
/**
 * MathExecutor: A simple and extensible math expressions calculator
 *
 * Copyright (c) Alexander Kiryukhin
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @link https://github.com/neonxp/MathExecutor
 *
 */

namespace Voxel\Vendor\NXP;

use \Voxel\Vendor\NXP\Classes\Calculator;
use \Voxel\Vendor\NXP\Classes\CustomFunction;
use \Voxel\Vendor\NXP\Classes\Operator;
use \Voxel\Vendor\NXP\Classes\Token;
use \Voxel\Vendor\NXP\Classes\Tokenizer;
use \Voxel\Vendor\NXP\Exception\DivisionByZeroException;
use \Voxel\Vendor\NXP\Exception\MathExecutorException;
use \Voxel\Vendor\NXP\Exception\UnknownVariableException;
use ReflectionException;

/**
 * Class MathExecutor
 * @package NXP
 */
class MathExecutor
{
    /**
     * Available variables
     *
     * @var array<string, float|string>
     */
    protected array $variables = [];

    /**
     * @var callable|null
     */
    protected $onVarNotFound = null;

    /**
     * @var callable|null
     */
    protected $onVarValidation = null;

    /**
     * @var Operator[]
     */
    protected array $operators = [];

    /**
     * @var array<string, CustomFunction>
     */
    protected array $functions = [];

    /**
     * @var array<string, Token[]>
     */
    protected array $cache = [];

    /**
     * Base math operators
     */
    public function __construct()
    {
        $this->addDefaults();
    }

    public function __clone()
    {
        $this->addDefaults();
    }

    /**
     * Add operator to executor
     *
     */
    public function addOperator(Operator $operator) : self
    {
        $this->operators[$operator->operator] = $operator;

        return $this;
    }

    /**
     * Execute expression
     *
     * @throws Exception\IncorrectExpressionException
     * @throws Exception\UnknownOperatorException
     * @throws UnknownVariableException
     * @throws Exception\IncorrectBracketsException
     * @return int|float|string|null
     */
    public function execute(string $expression, bool $cache = true)
    {
        $cacheKey = $expression;

        if (! \array_key_exists($cacheKey, $this->cache)) {
            $tokens = (new Tokenizer($expression, $this->operators))->tokenize()->buildReversePolishNotation();

            if ($cache) {
                $this->cache[$cacheKey] = $tokens;
            }
        } else {
            $tokens = $this->cache[$cacheKey];
        }

        $calculator = new Calculator($this->functions, $this->operators);

        return $calculator->calculate($tokens, $this->variables, $this->onVarNotFound);
    }

    /**
     * Add function to executor
     *
     * @param string $name Name of function
     * @param callable|null $function Function
     *
     * @throws ReflectionException
     * @throws Exception\IncorrectNumberOfFunctionParametersException
     */
    public function addFunction(string $name, ?callable $function = null) : self
    {
        $this->functions[$name] = new CustomFunction($name, $function);

        return $this;
    }

    /**
     * Get all vars
     *
     * @return array<string, float|string>
     */
    public function getVars() : array
    {
        return $this->variables;
    }

    /**
     * Get a specific var
     *
     * @throws UnknownVariableException if VarNotFoundHandler is not set
     */
    public function getVar(string $variable) : mixed
    {
        if (! \array_key_exists($variable, $this->variables)) {
            if ($this->onVarNotFound) {
                return \call_user_func($this->onVarNotFound, $variable);
            }

            throw new UnknownVariableException("Variable ({$variable}) not set");
        }

        return $this->variables[$variable];
    }

    /**
     * Add variable to executor. To set a custom validator use setVarValidationHandler.
     *
     * @throws MathExecutorException if the value is invalid based on the default or custom validator
     */
    public function setVar(string $variable, mixed $value) : self
    {
        if ($this->onVarValidation) {
            \call_user_func($this->onVarValidation, $variable, $value);
        }

        $this->variables[$variable] = $value;

        return $this;
    }

    /**
     * Test to see if a variable exists
     *
     */
    public function varExists(string $variable) : bool
    {
        return \array_key_exists($variable, $this->variables);
    }

    /**
     * Add variables to executor
     *
     * @param array<string, float|int|string> $variables
     * @param bool $clear Clear previous variables
     * @throws \Exception
     */
    public function setVars(array $variables, bool $clear = true) : self
    {
        if ($clear) {
            $this->removeVars();
        }

        foreach ($variables as $name => $value) {
            $this->setVar($name, $value);
        }

        return $this;
    }

    /**
     * Define a method that will be invoked when a variable is not found.
     * The first parameter will be the variable name, and the returned value will be used as the variable value.
     *
     *
     */
    public function setVarNotFoundHandler(callable $handler) : self
    {
        $this->onVarNotFound = $handler;

        return $this;
    }

    /**
     * Define a validation method that will be invoked when a variable is set using setVar.
     * The first parameter will be the variable name, and the second will be the variable value.
     * Set to null to disable validation.
     *
     * @param ?callable $handler throws a MathExecutorException in case of an invalid variable
     *
     */
    public function setVarValidationHandler(?callable $handler) : self
    {
        $this->onVarValidation = $handler;

        return $this;
    }

    /**
     * Remove variable from executor
     *
     */
    public function removeVar(string $variable) : self
    {
        unset($this->variables[$variable]);

        return $this;
    }

    /**
     * Remove all variables and the variable not found handler
     */
    public function removeVars() : self
    {
        $this->variables = [];
        $this->onVarNotFound = null;

        return $this;
    }

    /**
     * Get all registered operators to executor
     *
     * @return array<Operator> of operator class names
     */
    public function getOperators() : array
    {
        return $this->operators;
    }

    /**
     * Get all registered functions
     *
     * @return array<string, CustomFunction> containing callback and places indexed by
     *         function name
     */
    public function getFunctions() : array
    {
        return $this->functions;
    }

    /**
     * Remove a specific operator
     */
    public function removeOperator(string $operator) : self
    {
        unset($this->operators[$operator]);

        return $this;
    }

    /**
     * Set division by zero returns zero instead of throwing DivisionByZeroException
     */
    public function setDivisionByZeroIsZero() : self
    {
        $this->addOperator(new Operator('/', false, 180, static function($a, $b) { return 0 == $b ? 0 : $a / $b; }));

        return $this;
    }

    /**
     * Get cache array with tokens
     * @return array<string, Token[]>
     */
    public function getCache() : array
    {
        return $this->cache;
    }

    /**
     * Clear token's cache
     */
    public function clearCache() : self
    {
        $this->cache = [];

        return $this;
    }

    public function useBCMath(int $scale = 2) : self
    {
        \bcscale($scale);
        $this->addOperator(new Operator('+', false, 170, static function($a, $b) { return \bcadd("{$a}", "{$b}"); }));
        $this->addOperator(new Operator('-', false, 170, static function($a, $b) { return \bcsub("{$a}", "{$b}"); }));
        $this->addOperator(new Operator('uNeg', false, 200, static function($a) { return \bcsub('0.0', "{$a}"); }));
        $this->addOperator(new Operator('*', false, 180, static function($a, $b) { return \bcmul("{$a}", "{$b}"); }));
        $this->addOperator(new Operator('/', false, 180, static function($a, $b) {
            /** @todo PHP8: Use throw as expression -> static function($a, $b) { return 0 == $b ? throw new DivisionByZeroException() : $a / $b; } */
            if (0 == $b) {
                throw new DivisionByZeroException();
            }

            return \bcdiv("{$a}", "{$b}");
        }));
        $this->addOperator(new Operator('^', true, 220, static function($a, $b) { return \bcpow("{$a}", "{$b}"); }));
        $this->addOperator(new Operator('%', false, 180, static function($a, $b) { return \bcmod("{$a}", "{$b}"); }));

        return $this;
    }

    /**
     * Set default operands and functions
     * @throws ReflectionException
     */
    protected function addDefaults() : self
    {
        foreach ($this->defaultOperators() as $name => $operator) {
            [$callable, $priority, $isRightAssoc] = $operator;
            $this->addOperator(new Operator($name, $isRightAssoc, $priority, $callable));
        }

        foreach ($this->defaultFunctions() as $name => $callable) {
            $this->addFunction($name, $callable);
        }

        $this->onVarValidation = [$this, 'defaultVarValidation'];
        $this->variables = $this->defaultVars();

        return $this;
    }

    /**
     * Get the default operators
     *
     * @return array<string, array{callable, int, bool}>
     */
    protected function defaultOperators() : array
    {
        return [
          '+' => [static function($a, $b) { return $a + $b; }, 170, false],
          '-' => [static function($a, $b) { return $a - $b; }, 170, false],
          // unary positive token
          'uPos' => [static function($a) { return $a; }, 200, false],
          // unary minus token
          'uNeg' => [static function($a) { return 0 - $a; }, 200, false],
          '*' => [static function($a, $b) { return $a * $b; }, 180, false],
          '/' => [
            static function($a, $b) {
                /** @todo PHP8: Use throw as expression -> static function($a, $b) { return 0 == $b ? throw new DivisionByZeroException() : $a / $b; } */
                if (0 == $b) {
                    throw new DivisionByZeroException();
                }

                return $a / $b;
            },
            180,
            false
          ],
          '^' => [static function($a, $b) { return $a ** $b; }, 220, true],
          '%' => [static function($a, $b) { return $a % $b; }, 180, false],
          '&&' => [static function($a, $b) { return $a && $b; }, 100, false],
          '||' => [static function($a, $b) { return $a || $b; }, 90, false],
          '==' => [static function($a, $b) { return \is_string($a) || \is_string($b) ? 0 == \strcmp((string)$a, (string)$b) : $a == $b; }, 140, false],
          '!=' => [static function($a, $b) { return \is_string($a) || \is_string($b) ? 0 != \strcmp((string)$a, (string)$b) : $a != $b; }, 140, false],
          '>=' => [static function($a, $b) { return $a >= $b; }, 150, false],
          '>' => [static function($a, $b) { return $a > $b; }, 150, false],
          '<=' => [static function($a, $b) { return $a <= $b; }, 150, false],
          '<' => [static function($a, $b) { return $a < $b; }, 150, false],
          '!' => [static function($a) { return ! $a; }, 190, false],
        ];
    }

    /**
     * Gets the default functions as an array.  Key is function name
     * and value is the function as a closure.
     *
     * @return array<callable>
     */
    protected function defaultFunctions() : array
    {
        return [
          'abs' => static function($arg) { return \abs($arg); },
          'acos' => static function($arg) { return \acos($arg); },
          'acosh' => static function($arg) { return \acosh($arg); },
          'arcsin' => static function($arg) { return \asin($arg); },
          'arcctg' => static function($arg) { return M_PI / 2 - \atan($arg); },
          'arccot' => static function($arg) { return M_PI / 2 - \atan($arg); },
          'arccotan' => static function($arg) { return M_PI / 2 - \atan($arg); },
          'arcsec' => static function($arg) { return \acos(1 / $arg); },
          'arccosec' => static function($arg) { return \asin(1 / $arg); },
          'arccsc' => static function($arg) { return \asin(1 / $arg); },
          'arccos' => static function($arg) { return \acos($arg); },
          'arctan' => static function($arg) { return \atan($arg); },
          'arctg' => static function($arg) { return \atan($arg); },
          'array' => static function(...$args) { return $args; },
          'asin' => static function($arg) { return \asin($arg); },
          'atan' => static function($arg) { return \atan($arg); },
          'atan2' => static function($arg1, $arg2) { return \atan2($arg1, $arg2); },
          'atanh' => static function($arg) { return \atanh($arg); },
          'atn' => static function($arg) { return \atan($arg); },
          'avg' => static function($arg1, ...$args) {
              if (\is_array($arg1)) {
                  if (0 === \count($arg1)) {
                      throw new \InvalidArgumentException('avg() must have at least one argument!');
                  }

                  return \array_sum($arg1) / \count($arg1);
              }

              $args = [$arg1, ...$args];

              return \array_sum($args) / \count($args);
          },
          'bindec' => static function($arg) { return \bindec($arg); },
          'ceil' => static function($arg) { return \ceil($arg); },
          'cos' => static function($arg) { return \cos($arg); },
          'cosec' => static function($arg) { return 1 / \sin($arg); },
          'csc' => static function($arg) { return 1 / \sin($arg); },
          'cosh' => static function($arg) { return \cosh($arg); },
          'ctg' => static function($arg) { return \cos($arg) / \sin($arg); },
          'cot' => static function($arg) { return \cos($arg) / \sin($arg); },
          'cotan' => static function($arg) { return \cos($arg) / \sin($arg); },
          'cotg' => static function($arg) { return \cos($arg) / \sin($arg); },
          'ctn' => static function($arg) { return \cos($arg) / \sin($arg); },
          'decbin' => static function($arg) { return \decbin($arg); },
          'dechex' => static function($arg) { return \dechex($arg); },
          'decoct' => static function($arg) { return \decoct($arg); },
          'deg2rad' => static function($arg) { return \deg2rad($arg); },
          'exp' => static function($arg) { return \exp($arg); },
          'expm1' => static function($arg) { return \expm1($arg); },
          'floor' => static function($arg) { return \floor($arg); },
          'fmod' => static function($arg1, $arg2) { return \fmod($arg1, $arg2); },
          'hexdec' => static function($arg) { return \hexdec($arg); },
          'hypot' => static function($arg1, $arg2) { return \hypot($arg1, $arg2); },
          'if' => function($expr, $trueval, $falseval) {
              if (true === $expr || false === $expr) {
                  $exres = $expr;
              } else {
                  $exres = $this->execute($expr);
              }

              if ($exres) {
                  return $this->execute($trueval);
              }

              return $this->execute($falseval);
          },
          'intdiv' => static function($arg1, $arg2) { return \intdiv($arg1, $arg2); },
          'ln' => static function($arg1, $arg2 = M_E) { return \log($arg1, $arg2); },
          'lg' => static function($arg) { return \log10($arg); },
          'log' => static function($arg1, $arg2 = M_E) { return \log($arg1, $arg2); },
          'log10' => static function($arg) { return \log10($arg); },
          'log1p' => static function($arg) { return \log1p($arg); },
          'max' => static function($arg1, ...$args) {
              if (\is_array($arg1) && 0 === \count($arg1)) {
                  throw new \InvalidArgumentException('max() must have at least one argument!');
              }

              return \max(\is_array($arg1) ? $arg1 : [$arg1, ...$args]);
          },
          'median' => static function($arg1, ...$args) {
              if (\is_array($arg1)) {
                  if (0 === \count($arg1)) {
                      throw new \InvalidArgumentException('Array must contain at least one element!');
                  }

                  $finalArgs = $arg1;
              } else {
                  $finalArgs = [$arg1, ...$args];
              }

              $count = \count($finalArgs);
              \sort($finalArgs);
              $index = \floor($count / 2);

              return ($count & 1) ? $finalArgs[$index] : ($finalArgs[$index - 1] + $finalArgs[$index]) / 2;
          },
          'min' => static function($arg1, ...$args) {
              if (\is_array($arg1) && 0 === \count($arg1)) {
                  throw new \InvalidArgumentException('min() must have at least one argument!');
              }

              return \min(\is_array($arg1) ? $arg1 : [$arg1, ...$args]);
          },
          'octdec' => static function($arg) { return \octdec($arg); },
          'pi' => static function() { return M_PI; },
          'pow' => static function($arg1, $arg2) { return $arg1 ** $arg2; },
          'rad2deg' => static function($arg) { return \rad2deg($arg); },
          'round' => static function($num, int $precision = 0) { return \round($num, $precision); },
          'sin' => static function($arg) { return \sin($arg); },
          'sinh' => static function($arg) { return \sinh($arg); },
          'sec' => static function($arg) { return 1 / \cos($arg); },
          'sqrt' => static function($arg) { return \sqrt($arg); },
          'tan' => static function($arg) { return \tan($arg); },
          'tanh' => static function($arg) { return \tanh($arg); },
          'tn' => static function($arg) { return \tan($arg); },
          'tg' => static function($arg) { return \tan($arg); },
        ];
    }

    /**
     * Returns the default variables names as key/value pairs
     *
     * @return array<string, float>
     */
    protected function defaultVars() : array
    {
        return [
          'pi' => 3.14159265359,
          'e' => 2.71828182846
        ];
    }

    /**
     * Default variable validation, ensures that the value is a scalar or array.
     * @throws MathExecutorException if the value is not a scalar
     */
    protected function defaultVarValidation(string $variable, mixed $value) : void
    {
        if (! \is_scalar($value) && ! \is_array($value) && null !== $value) {
            $type = \gettype($value);

            throw new MathExecutorException("Variable ({$variable}) type ({$type}) is not scalar or array!");
        }
    }
}
