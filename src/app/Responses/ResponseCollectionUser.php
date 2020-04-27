<?php


namespace App\app\Responses;


use App\Components\ResponseCollection;
use App\interfaces\ResponseCollectionInterface;

/**
 * Class ResponseUser
 * @package App\app\Responses
 */
class ResponseCollectionUser extends ResponseCollection implements ResponseCollectionInterface
{

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [

            'status'=>true,
            'user'=>$this->collection
        ];
    }
}