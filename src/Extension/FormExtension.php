<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extension;

use BeastBytes\View\Latte\Extension\Node\FormNode;
use Latte\Extension;

class FormExtension extends Extension
{
    public function getFilters(): array
    {
        return [
            'acceptCharset' => [Filters::class, 'acceptCharset'],
            'autocomplete' => [Filters::class, 'autocomplete'],
            'csrf' => [Filters::class, 'csrf'],
            'enctype' => [Filters::class, 'enctype'],
            'method' => [Filters::class, 'method'],
            'noValidate' => [Filters::class, 'noValidate'],
            'target' => [Filters::class, 'target'],
        ];
    }

    public function getTags(): array
    {
        return [
            'form' => [FormNode::class, 'create'],
        ];
    }
}



/**
 * Latte v3 extension for Nette Forms.
 */
final class FormsExtension extends Latte\Extension
{
    public function getTags(): array
    {
        return [
            'form' => Nodes\FormNode::create(...),
            'formContext' => Nodes\FormNode::create(...),
            'formContainer' => Nodes\FormContainerNode::create(...),
            'label' => Nodes\LabelNode::create(...),
            'input' => Nodes\InputNode::create(...),
            'inputError' => Nodes\InputErrorNode::create(...),
            'formPrint' => Nodes\FormPrintNode::create(...),
            'formClassPrint' => Nodes\FormPrintNode::create(...),
            'n:name' => fn(Latte\Compiler\Tag $tag) => yield from strtolower($tag->htmlElement->name) === 'form'
                ? Nodes\FormNNameNode::create($tag)
                : Nodes\FieldNNameNode::create($tag),
        ];
    }


    public function getProviders(): array
    {
        return [
            'forms' => new Runtime,
        ];
    }


    public function getCacheKey(Latte\Engine $engine): mixed
    {
        return 2;
    }
}