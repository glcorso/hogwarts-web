<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart
 * @author      Andrew Smith
 * @link        http://www.slimframework.com
 * @copyright   2013 Josh Lockhart
 * @version     0.1.3
 * @package     SlimViews
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Lidere\Twig\Extension;

use Slim\Slim;
use DebugBar\StandardDebugBar;
use DebugBar\Bridge\SlimCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use Lidere\Core;

class View extends \Twig_Extension
{
    public function getName()
    {
        return 'view';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('serialize', array($this, 'serialize')),
            new \Twig_SimpleFunction('unserialize', array($this, 'unserialize')),
            new \Twig_SimpleFunction('mask', array($this, 'mask')),
            new \Twig_SimpleFunction('loadcss', array($this, 'loadcss')),
            new \Twig_SimpleFunction('loadjs', array($this, 'loadjs')),
        );
    }

    public function serialize($value = null)
    {
        return serialize($value);
    }

    public function unserialize($str = null)
    {
        return unserialize($str);
    }

    public function mask($str = null, $mask = '')
    {
        return Core::insereMascara($str, $mask);
    }

    public function loadcss()
    {
        return \Lidere\Assets::loadCss();
    }

    public function loadjs()
    {
        return \Lidere\Assets::loadJs();
    }
}
