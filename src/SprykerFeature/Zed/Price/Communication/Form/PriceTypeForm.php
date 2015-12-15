<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Price\Communication\Form;

use Spryker\Zed\Gui\Communication\Form\AbstractForm;
use Symfony\Component\Validator\Constraints as Assert;

class PriceTypeForm extends AbstractForm
{

    /**
     * @return void
     */
    protected function buildFormFields()
    {
        // @todo: Implement buildFormFields() method.
    }

    /**
     * @return void
     */
    protected function populateFormFields()
    {
        // @todo: Implement populateFormFields() method.
    }

    /**
     * @return array
     */
    public function addFormFields()
    {
        $this->addField('name')
            ->setConstraints([
                new Assert\Type([
                    'type' => 'string',
                ]),
                new Assert\NotBlank(),
            ]);
    }

    /**
     * @return array
     */
    public function getDefaultData()
    {
        return [];
    }

}
