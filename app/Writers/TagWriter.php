<?php

namespace App\Writers;

use AmoCRM\Collections\BaseApiCollection;
use App\Models\Tag;

class TagWriter extends Writer
{

    public function write_by_id(?int $id)
    {
        // TODO: Implement write_by_id() method.
    }

    public function write_collection(?BaseApiCollection $collection)
    {
        if ($collection === null)
            return null;

        $tags_ids = [];
        for ($j = 0; $j < $collection->count(); $j++){

            if($collection[$j] == null)
                continue;

            $tags_ids[] = $collection[$j]->getId();

            if (Tag::find($collection[$j]->getId()))
                continue;

            $tag = new Tag();
            $tag->id = $collection[$j]->getId();
            $tag->color = $collection[$j]->getColor();
            $tag->name = $collection[$j]->getName();
            $tag->save();
        }
        return $tags_ids;
    }
}
