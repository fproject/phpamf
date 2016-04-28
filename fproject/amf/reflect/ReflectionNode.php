<?php
///////////////////////////////////////////////////////////////////////////////
//
// © Copyright f-project.net 2010-present.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
///////////////////////////////////////////////////////////////////////////////

namespace fproject\amf\reflect;
/**
 * Node Tree class for AMF server reflection operations
 *
 */
class ReflectionNode
{
    /**
     * Node value
     * @var mixed
     */
    protected $_value = null;

    /**
     * Array of child nodes (if any)
     * @var array
     */
    protected $_children = [];

    /**
     * Parent node (if any)
     * @var ReflectionNode
     */
    protected $_parent = null;

    /**
     * Constructor
     *
     * @param mixed $value
     * @param ReflectionNode $parent Optional
     * @return ReflectionNode
     */
    public function __construct($value, ReflectionNode $parent = null)
    {
        $this->_value = $value;
        if (null !== $parent) {
            $this->setParent($parent, true);
        }

        return $this;
    }

    /**
     * Set parent node
     *
     * @param ReflectionNode $node
     * @param boolean $new Whether or not the child node is newly created
     * and should always be attached
     * @return void
     */
    public function setParent(ReflectionNode $node, $new = false)
    {
        $this->_parent = $node;

        if ($new) {
            $node->attachChild($this);
            return;
        }
    }

    /**
     * Create and attach a new child node
     *
     * @param mixed $value
     * @access public
     * @return ReflectionNode New child node
     */
    public function createChild($value)
    {
        $child = new self($value, $this);

        return $child;
    }

    /**
     * Attach a child node
     *
     * @param ReflectionNode $node
     * @return void
     */
    public function attachChild(ReflectionNode $node)
    {
        $this->_children[] = $node;

        if ($node->getParent() !== $this) {
            $node->setParent($this);
        }
    }

    /**
     * Return an array of all child nodes
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Does this node have children?
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->_children) > 0;
    }

    /**
     * Return the parent node
     *
     * @return null|ReflectionNode
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Return the node's current value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Set the node value
     *
     * @param mixed $value
     * @return void
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Retrieve the bottommost nodes of this node's tree
     *
     * Retrieves the bottommost nodes of the tree by recursively calling
     * getEndPoints() on all children. If a child is null, it returns the parent
     * as an end point.
     *
     * @return array
     */
    public function getEndPoints()
    {
        $endPoints = [];
        if (!$this->hasChildren()) {
            return $endPoints;
        }

        foreach ($this->_children as $child) {
            $value = $child->getValue();

            if (null === $value) {
                $endPoints[] = $this;
            } elseif ((null !== $value)
                && $child->hasChildren())
            {
                $childEndPoints = $child->getEndPoints();
                if (!empty($childEndPoints)) {
                    $endPoints = array_merge($endPoints, $childEndPoints);
                }
            } elseif ((null !== $value) && !$child->hasChildren()) {
                $endPoints[] = $child;
            }
        }

        return $endPoints;
    }
}
