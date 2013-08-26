<?php

namespace movi\Application\UI;


class AjaxControl extends Control
{

    public function attached($presenter)
    {
        parent::attached($presenter);

        if ($presenter instanceof Presenter && $presenter->isAjax()) {
            $this->invalidateControl();
        }
    }

}