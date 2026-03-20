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
use Webmozart\Assert\Assert;
final class Table_Accessor implements Table_Accessor_Interface
{
    public function get_row_with_fields(Node_Element $table, array $fields)
    {
        try {
            return $this->get_rows_with_fields($table, $fields)[0];
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException('Could not find row with given fields', 0, $exception);
        }
    }
    public function get_rows_with_fields(Node_Element $table, array $fields)
    {
        try {
            return $this->find_rows_with_fields($table, $fields);
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException('Could not find any row with given fields', 0, $exception);
        }
    }
    public function get_field_from_row(Node_Element $table, Node_Element $row, $field)
    {
        $column_index = $this->get_column_index($table, $field);
        $columns = $row->find_all('css', 'td,th');
        if (!isset($columns[$column_index])) {
            throw new \InvalidArgumentException(sprintf('Could not find column with index %d', $column_index));
        }
        return $columns[$column_index];
    }
    /**
     * @return mixed[]
     */
    public function get_indexed_column(Node_Element $table, $field_name): array
    {
        $column_index = $this->get_column_index($table, $field_name);
        $rows = $table->find_all('css', 'tbody > tr');
        Assert::not_empty($rows, 'There are no rows!');
        $column_fields = [];
        /** @var NodeElement $row */
        foreach ($rows as $row) {
            $cells = $row->find_all('css', 'td');
            $column_fields[] = $cells[$column_index]->get_text();
        }
        return $column_fields;
    }
    /**
     * @return mixed[]
     */
    public function get_sortable_headers(Node_Element $table): array
    {
        $sortable_headers = $table->find_all('css', 'th.sortable');
        Assert::not_empty($sortable_headers, 'There are no sortable headers.');
        $sortable_array = [];
        /** @var NodeElement $sortable */
        foreach ($sortable_headers as $sortable) {
            $field_name = $this->get_column_field_name($sortable);
            $sortable_array[$field_name] = $sortable;
        }
        return $sortable_array;
    }
    public function count_table_body_rows(Node_Element $table): int
    {
        return count($table->find_all('css', 'tbody > tr'));
    }
    /**
     * @return NodeElement[]
     *
     * @throws \InvalidArgumentException If rows were not found
     */
    private function find_rows_with_fields(Node_Element $table, array $fields): array
    {
        $rows = $table->find_all('css', 'tr');
        Assert::not_empty($rows, 'There are no rows!');
        $fields = $this->replace_column_names_with_column_indexes($table, $fields);
        $matched_rows = [];
        /** @var NodeElement[] $rows */
        $rows = $table->find_all('css', 'tr');
        foreach ($rows as $row) {
            /** @var NodeElement[] $columns */
            $columns = $row->find_all('css', 'td, th');
            if ($this->has_row_fields($columns, $fields)) {
                $matched_rows[] = $row;
            }
        }
        return $matched_rows;
    }
    private function has_row_fields(array $columns, array $fields): bool
    {
        foreach ($fields as $index => $searched_value) {
            if (!isset($columns[$index])) {
                return false;
            }
            $searched_value = (string) $searched_value;
            $searched_value = trim($searched_value);
            if (str_starts_with($searched_value, '%') && strlen($searched_value) - 1 === strrpos($searched_value, '%')) {
                $searched_value = substr($searched_value, 1, -2);
            }
            if (!$this->contains_searched_value($columns[$index]->get_text(), $searched_value)) {
                return false;
            }
        }
        return true;
    }
    /**
     * @param string[] $fields
     *
     * @return string[]
     *
     * @throws \Exception
     */
    private function replace_column_names_with_column_indexes(Node_Element $table, array $fields): array
    {
        $replaced_fields = [];
        foreach ($fields as $column_name => $expected_value) {
            $column_index = $this->get_column_index($table, $column_name);
            $replaced_fields[$column_index] = $expected_value;
        }
        return $replaced_fields;
    }
    /**
     * @param string $fieldName
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    private function get_column_index(Node_Element $table, $field_name)
    {
        $rows = $table->find_all('css', 'tr');
        Assert::not_empty($rows, 'There are no rows!');
        /** @var NodeElement $headerRow */
        $header_row = $rows[0];
        $headers = $header_row->find_all('css', 'th,td');
        /** @var NodeElement $column */
        foreach ($headers as $index => $column) {
            $column_field_name = $this->get_column_field_name($column);
            if ($field_name === $column_field_name) {
                return $index;
            }
        }
        throw new \InvalidArgumentException(sprintf('Column with name "%s" not found!', $field_name));
    }
    /**
     * @param string $sourceText
     *
     */
    private function contains_searched_value($source_text, string $searched_value): bool
    {
        return false !== stripos(trim($source_text), $searched_value);
    }
    private function get_column_field_name(Node_Element $column): string
    {
        return $column->get_attribute('data-test-table') ?? preg_replace('/.*sylius-table-column-([^ ]+).*$/', '\1', $column->get_attribute('class'));
    }
}