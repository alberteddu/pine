<?php

namespace Pine\Twig;

use Twig_Environment;

class ReusableTwigEnvironment extends Twig_Environment
{
    private $entropy = 0;

    public function getTemplateClass($name, $index = null)
    {
        $className = parent::getTemplateClass($name, $index);
        $start = $className;
        $end = '';

        if (null !== $index) {
            $explode = explode('_', $className);
            $start = implode('_', array_slice($explode, 0, -1));
            $end = '_' . $explode[count($explode) - 1];
        }

        return $start . $this->entropy . $end;
    }

    public function increaseEntropy()
    {
        $this->entropy++;
    }
}
