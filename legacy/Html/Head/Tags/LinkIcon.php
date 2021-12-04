<?php

namespace Nip\Html\Head\Tags;

/**
 * Class Link.
 */
class LinkIcon extends Link
{

    /**
     * LinkIcon constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setRel('icon');
        $this->setType('image/png');
    }

    /**
     * @param $value
     *
     * @return bool|$this|AbstractTag
     */
    public function setSizes($value)
    {
        return $this->setAttribute('sizes', $value);
    }

    protected function initValidAttributes()
    {
        parent::initValidAttributes();
        $this->addValidAttributes('sizes');
    }
}
