<?php

namespace UsefulDates\Traits;

use UsefulDates\Abstracts\ExtensionAbstract;

trait Extensions
{
    /**
     * @throws \Exception
     */
    public function addExtension($extension): self
    {
        if (get_parent_class($extension) !== ExtensionAbstract::class) {
            throw new \Exception('Extension must extend \UsefulDates\Abstracts\ExtensionAbstract');
        }

        return $this;
    }
}
