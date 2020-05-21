<?php


namespace App\Components\Cart;

use App\Components\Database\Model;
use App\Components\Http\Session;
use JsonException;

/**
 * Class Cart
 * @package App\Components\Cart
 */
class Cart
{
    /**
     * @var Session $session
     */
    private Session $session;
    /**
     * @var string $sessionPrefix
     */
    private string  $sessionPrefix = 'cart_sessions';

    /**
     * Cart constructor.
     */
    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @param Model $model
     * @param int $quantity
     * @return Cart
     * @throws JsonException
     *
     */
    public function add(Model $model, int $quantity = 1): Cart
    {
        return $this->addToCart($model, $quantity);
    }


    /**
     * @param int $primaryKeyValue
     * @return mixed
     */
    public function find(int $primaryKeyValue)
    {
        return $this->session->get($this->sessionPrefix)[$primaryKeyValue] ?? null;
    }

    /**
     * @param Model $model
     * @param int $quantity
     * @return Cart
     * @throws JsonException
     */
    private function addToCart(Model $model, int $quantity = 1): Cart
    {
        $this->session->put(
            $this->sessionPrefix,
            $model->getPrimaryKeyValue(),
            array_merge($model->toArray(), ['quantity' => $quantity])
        );
        return $this;
    }

    /**
     * return all item of  on the cart
     * @return array|mixed|string
     */
    public function get()
    {
        return $this->session->get($this->sessionPrefix) ?? [];
    }

    /**
     * @param string $priceField
     * @param string $quantityField
     * @return float
     */
    public function total(string $priceField = 'price', string $quantityField = 'quantity'): float
    {
        $total = 0;
        foreach ($this->get() as $item) {
            $total += $item[$priceField] * $item[$quantityField];
        }
        return $total;
    }


    /**
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $this->session->deleteByIndex($this->sessionPrefix, $model->getPrimaryKeyValue());
    }

    /**
     * @return bool
     */
    public function deleteAll():bool
    {
        return $this->session->delete($this->sessionPrefix);
    }
}
