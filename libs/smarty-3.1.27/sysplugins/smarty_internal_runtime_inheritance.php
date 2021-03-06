<?php

/**
 * Inheritance Runtime Methods processBlock, endChild, init
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 *
 **/
class Smarty_Internal_Runtime_Inheritance
{

    /**
     * State machine
     * - 0 idle next extends will create a new inheritance tree
     * - 1 processing child template
     * - 2 wait for next inheritance template
     * - 3 assume parent template, if child will loaded goto state 1
     *     a call to a sub template resets the state to 0
     *
     * @var int
     */
    public $state = 0;

    /**
     * Array of root child {block} objects
     *
     * @var Smarty_Internal_Block[]
     */
    public $childRoot = [];

    /**
     * inheritance template nesting level
     *
     * @var int
     */
    public $inheritanceLevel = 0;

    /**
     * inheritance template index
     *
     * @var int
     */
    public $tplIndex = - 1;

    /**
     * Array of template source objects
     * - key template index
     *
     * @var Smarty_Template_Source[]
     */
    public $sources = [];

    /**
     * Call stack of block objects
     *
     * @var Smarty_Internal_Block[]
     */
    public $blockCallStack = [];

    /**
     * Initialize inheritance
     *
     * @param \Smarty_Internal_Template $tpl        template object of caller
     * @param bool                      $initChild  if true init for child template
     * @param array                     $blockNames outer level block name
     *
     */
    public function init(Smarty_Internal_Template $tpl, $initChild, $blockNames = [])
    {
        // if called while executing parent template it must be a sub-template with new inheritance root
        if ($initChild && $this->state == 3 && (strpos($tpl->template_resource, 'extendsall') === false)) {
            $tpl->inheritance = new Smarty_Internal_Runtime_Inheritance();
            $tpl->inheritance->init($tpl, $initChild, $blockNames);
            return;
        }
        // start of child sub template(s)
        if ($initChild) {
            $this->state = 1;
            if (!$this->inheritanceLevel) {
                //grab any output of child templates
                ob_start();
            }
            $this->inheritanceLevel ++;
            $tpl->startRenderCallbacks[ 'inheritance' ] = [$this, 'subTemplateStart'];
            $tpl->endRenderCallbacks[ 'inheritance' ] = [$this, 'subTemplateEnd'];
        }
        // in parent state {include} will not increment template index
        if ($this->state != 3) {
            $this->tplIndex ++;
            $this->sources[ $this->tplIndex ] = $tpl->source;
        }
        // if state was waiting for parent change state to parent
        if ($this->state == 2) {
            $this->state = 3;
        }
    }

    /**
     * End of child template(s)
     * - if outer level is reached flush output buffer and switch to wait for parent template state
     *
     * @param \Smarty_Internal_Template $tpl template object of caller
     */
    public function endChild(Smarty_Internal_Template $tpl)
    {
        $this->inheritanceLevel --;
        if (!$this->inheritanceLevel) {
            ob_end_clean();
            $this->state = 2;
        }
    }

    /**
     * Smarty_Internal_Block constructor.
     * - if outer level {block} of child template ($state == 1) save it as child root block
     * - otherwise process inheritance and render
     *
     * @param \Smarty_Internal_Template $tpl
     * @param                           $className
     * @param string                    $name
     * @param int|null                  $tplIndex index of outer level {block} if nested
     */
    public function instanceBlock(Smarty_Internal_Template $tpl, $className, $name, $tplIndex = null)
    {
        $block = new $className($name, $tplIndex ? $tplIndex : $this->tplIndex);
        if (isset($this->childRoot[ $name ])) {
            $block->child = $this->childRoot[ $name ];
        }
        if ($this->state == 1) {
            $this->childRoot[ $name ] = $block;
            return;
        }
        // make sure we got child block of child template of current block
        while ($block->child && $block->tplIndex <= $block->child->tplIndex) {
            $block->child = $block->child->child;
        }
        $this->process($tpl, $block);
    }

    /**
     * Goto child block or render this
     *
     * @param \Smarty_Internal_Template   $tpl
     * @param \Smarty_Internal_Block      $block
     * @param \Smarty_Internal_Block|null $parent
     *
     * @throws \SmartyException
     */
    public function process(Smarty_Internal_Template $tpl, Smarty_Internal_Block $block,
                            Smarty_Internal_Block $parent = null)
    {
        if ($block->hide && !isset($block->child)) {
            return;
        }
        if (isset($block->child) && $block->child->hide && !isset($block->child->child)) {
            $block->child = null;
        }
        $block->parent = $parent;
        if ($block->append && !$block->prepend && isset($parent)) {
            $this->callParent($tpl, $block);
        }
        if ($block->callsChild || !isset($block->child) || ($block->child->hide && !isset($block->child->child))) {
            $block->subTemplateNesting = 0;
            $this->blockCallStack[] = $block;
            $block->callBlock($tpl);
            array_pop($this->blockCallStack);
        } else {
            $this->process($tpl, $block->child, $block);
        }
        if ($block->prepend && isset($parent)) {
            $this->callParent($tpl, $block);
            if ($block->append) {
                if ($block->callsChild || !isset($block->child) ||
                    ($block->child->hide && !isset($block->child->child))
                ) {
                    $block->subTemplateNesting = 0;
                    $this->blockCallStack[] = $block;
                    $block->callBlock($tpl);
                    array_pop($this->blockCallStack);
                } else {
                    $this->process($tpl, $block->child, $block);
                }
            }
        }
        $block->parent = null;
    }

    /**
     * Render child on {$smarty.block.child}
     *
     * @param \Smarty_Internal_Template $tpl
     * @param \Smarty_Internal_Block    $block
     */
    public function callChild(Smarty_Internal_Template $tpl, Smarty_Internal_Block $block)
    {
        if (isset($block->child)) {
            $this->process($tpl, $block->child, $block);
        }
    }

    /**
     * Render parent on {$smarty.block.parent} or {block append/prepend}     *
     *
     * @param \Smarty_Internal_Template $tpl
     * @param \Smarty_Internal_Block    $block
     *
     * @throws \SmartyException
     */
    public function callParent(Smarty_Internal_Template $tpl, Smarty_Internal_Block $block)
    {
        if (isset($block->parent)) {
            $block->parent->subTemplateNesting = 0;
            $this->blockCallStack[] = $block->parent;
            $block->parent->callBlock($tpl);
            array_pop($this->blockCallStack);
        } else {
            throw new SmartyException("inheritance: illegal {\$smarty.block.parent} or {block append/prepend} used in parent template '{$tpl->inheritance->sources[$block->tplIndex]->filepath}' block '{$block->name}'");
        }
    }

    /**
     * Return source filepath of current {block} if not in sub-template
     *
     * @return bool|string  filepath or false
     */
    public function getBlockFilepath()
    {
        $count = count($this->blockCallStack);
        if ($count && $this->blockCallStack[ $count - 1 ]->subTemplateNesting === 0) {
            return $this->sources[ $this->blockCallStack[ $count - 1 ]->tplIndex ]->filepath;
        }
        return false;
    }

    /**
     *  Increment sub-template nesting count in current block object
     */
    public function subTemplateStart()
    {
        $count = count($this->blockCallStack);
        if ($count) {
            $this->blockCallStack[ $count - 1 ]->subTemplateNesting ++;
        }
    }

    /**
     *  Decrement sub-template nesting count in current block object
     */
    public function subTemplateEnd()
    {
        $count = count($this->blockCallStack);
        if ($count && $this->blockCallStack[ $count - 1 ]->subTemplateNesting) {
            $this->blockCallStack[ $count - 1 ]->subTemplateNesting --;
        }
    }
}
