<?php
/**
 * States
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that is bundled with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@uni-alteri.com so we can send you a copy immediately.
 *
 * @package     States
 * @subpackage  Demo
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     0.9.2
 */

namespace demo\UniAlteri\Article;

use UniAlteri\States\Proxy;
use UniAlteri\States\Proxy\Exception;

class Article extends Proxy\Integrated
{
    /**
     * Article's data
     * @var array
     */
    protected $_data = array();

    /**
     * Get an article's attribute
     * @param string $name
     * @return mixed
     */
    protected function _getAttribute($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        return null;
    }

    /**
     * Update an article's attribute
     * @param string $name
     * @param mixed $value
     */
    public function _setAttribute($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * To initialize this article with some data
     * @param array $data
     */
    public function __construct($data=array())
    {
        $this->_data = $data;
        parent::__construct();
        //If the article is published, load the state Published, else load the state Draft
        if (false === $this->isPublished()) {
            $this->enableState('Draft');
        } else {
            $this->enableState('Published');
        }
    }
}