<?php

namespace Kompo;

use Kompo\SelectLinks;

class SelectButtons extends SelectLinks
{
    protected function vlInitialize($label)
    {
        parent::vlInitialize($label);

        $this->noInputWrapper();

        $this->containerClass('row');

        $this->optionClass('col');

        $this->optionInnerClass('vlInputWrapper');
    }
}
