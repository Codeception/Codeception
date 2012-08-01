<?php

namespace Behat\Mink\Element;

use Behat\Mink\Exception\ElementNotFoundException;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Traversable element.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class TraversableElement extends Element
{
    /**
     * Finds element by it's id.
     *
     * @param string $id element id
     *
     * @return NodeElement|null
     */
    public function findById($id)
    {
        $id = $this->getSession()->getSelectorsHandler()->xpathLiteral($id);

        return $this->find('xpath', "//*[@id=$id]");
    }

    /**
     * Checks whether document has a link with specified locator.
     *
     * @param string $locator link id, title, text or image alt
     *
     * @return Boolean
     */
    public function hasLink($locator)
    {
        return null !== $this->findLink($locator);
    }

    /**
     * Finds link with specified locator.
     *
     * @param string $locator link id, title, text or image alt
     *
     * @return NodeElement|null
     */
    public function findLink($locator)
    {
        return $this->find('named', array(
            'link', $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
        ));
    }

    /**
     * Clicks link with specified locator.
     *
     * @param string $locator link id, title, text or image alt
     *
     * @throws ElementNotFoundException
     */
    public function clickLink($locator)
    {
        $link = $this->findLink($locator);

        if (null === $link) {
            throw new ElementNotFoundException(
                $this->getSession(), 'link', 'id|title|alt|text', $locator
            );
        }

        $link->click();
    }

    /**
     * Checks whether document has a button (input[type=submit|image|button], button) with specified locator.
     *
     * @param string $locator button id, value or alt
     *
     * @return Boolean
     */
    public function hasButton($locator)
    {
        return null !== $this->findButton($locator);
    }

    /**
     * Finds button (input[type=submit|image|button], button) with specified locator.
     *
     * @param string $locator button id, value or alt
     *
     * @return NodeElement|null
     */
    public function findButton($locator)
    {
        return $this->find('named', array(
            'button', $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
        ));
    }

    /**
     * Presses button (input[type=submit|image|button], button) with specified locator.
     *
     * @param string $locator button id, value or alt
     *
     * @throws ElementNotFoundException
     */
    public function pressButton($locator)
    {
        $button = $this->findButton($locator);

        if (null === $button) {
            throw new ElementNotFoundException(
                $this->getSession(), 'button', 'id|name|title|alt|value', $locator
            );
        }

        $button->press();
    }

    /**
     * Checks whether document has a field (input, textarea, select) with specified locator.
     *
     * @param string $locator input id, name or label
     *
     * @return Boolean
     */
    public function hasField($locator)
    {
        return null !== $this->findField($locator);
    }

    /**
     * Finds field (input, textarea, select) with specified locator.
     *
     * @param string $locator input id, name or label
     *
     * @return NodeElement|null
     */
    public function findField($locator)
    {
        return $this->find('named', array(
            'field', $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
        ));
    }

    /**
     * Fills in field (input, textarea, select) with specified locator.
     *
     * @param string $locator input id, name or label
     * @param string $value   value
     *
     * @throws ElementNotFoundException
     */
    public function fillField($locator, $value)
    {
        $field = $this->findField($locator);

        if (null === $field) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form field', 'id|name|label|value', $locator
            );
        }

        $field->setValue($value);
    }

    /**
     * Checks whether document has a checkbox with specified locator, which is checked.
     *
     * @param string $locator input id, name or label
     *
     * @return Boolean
     */
    public function hasCheckedField($locator)
    {
        $field = $this->findField($locator);

        return null !== $field && $field->isChecked();
    }

    /**
     * Checks whether document has a checkbox with specified locator, which is unchecked.
     *
     * @param string $locator input id, name or label
     *
     * @return Boolean
     */
    public function hasUncheckedField($locator)
    {
        $field = $this->findField($locator);

        return null !== $field && !$field->isChecked();
    }

    /**
     * Checks checkbox with specified locator.
     *
     * @param string $locator input id, name or label
     *
     * @throws ElementNotFoundException
     */
    public function checkField($locator)
    {
        $field = $this->findField($locator);

        if (null === $field) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form field', 'id|name|label|value', $locator
            );
        }

        $field->check();
    }

    /**
     * Unchecks checkbox with specified locator.
     *
     * @param string $locator input id, name or label
     *
     * @throws ElementNotFoundException
     */
    public function uncheckField($locator)
    {
        $field = $this->findField($locator);

        if (null === $field) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form field', 'id|name|label|value', $locator
            );
        }

        $field->uncheck();
    }

    /**
     * Checks whether document has a select field with specified locator.
     *
     * @param string $locator select id, name or label
     *
     * @return Boolean
     */
    public function hasSelect($locator)
    {
        return $this->has('named', array(
            'select', $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
        ));
    }

    /**
     * Selects option from select field with specified locator.
     *
     * @param string  $locator  input id, name or label
     * @param string  $value    option value
     * @param Boolean $multiple select multiple options
     *
     * @throws ElementNotFoundException
     */
    public function selectFieldOption($locator, $value, $multiple = false)
    {
        $field = $this->findField($locator);

        if (null === $field) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form field', 'id|name|label|value', $locator
            );
        }

        $field->selectOption($value, $multiple);
    }

    /**
     * Checks whether document has a table with specified locator.
     *
     * @param string $locator table id or caption
     *
     * @return Boolean
     */
    public function hasTable($locator)
    {
        return $this->has('named', array(
            'table', $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
        ));
    }

    /**
     * Attach file to file field with specified locator.
     *
     * @param string $locator input id, name or label
     * @param string $path    path to file
     *
     * @throws ElementNotFoundException
     */
    public function attachFileToField($locator, $path)
    {
        $field = $this->findField($locator);

        if (null === $field) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form field', 'id|name|label|value', $locator
            );
        }

        $field->attachFile($path);
    }
}
