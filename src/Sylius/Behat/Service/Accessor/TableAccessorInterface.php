<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Behat\Service\Accessor;

use Behat\Mink\Element\Node_Element;
interface Table_Accessor_Interface
{
    /**
     * @return NodeElement
     *
     * @throws \InvalidArgumentException If row cannot be found
     */
    public function get_row_with_fields(Node_Element $table, array $fields);
    /**
     * @return NodeElement[]
     *
     * @throws \InvalidArgumentException If there is no rows fulfilling given conditions
     */
    public function get_rows_with_fields(Node_Element $table, array $fields);
    /**
     * @param string $fieldName
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function get_indexed_column(Node_Element $table, $field_name);
    /**
     * @return NodeElement[]
     */
    public function get_sortable_headers(Node_Element $table);
    /**
     * @param string $field
     *
     * @return NodeElement
     */
    public function get_field_from_row(Node_Element $table, Node_Element $row, $field);
    /**
     * @return int
     */
    public function count_table_body_rows(Node_Element $table);
}