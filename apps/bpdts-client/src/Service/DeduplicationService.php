<?php
declare(strict_types = 1);

namespace App\Service;

class DeduplicationService
{
    public function deduplicate(array $lists, callable $getId, callable $addResult): void
    {
        $includedItems = [];
        foreach ($lists as $list) {
            foreach ($list as $item) {
                $id = $getId($item);
                if (!in_array($id, $includedItems)) {
                    $addResult($item);
                    $includedItems[] = $id;
                }
            }
        }
    }
}
