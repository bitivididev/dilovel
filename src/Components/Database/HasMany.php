<?php


namespace App\Components\Database;

use App\Components\Collection\Collection;

class HasMany
{
    /**
     * @var string
     */
    private string $foreignKey;
    /**
     * @var string
     */
    private string $primaryKey;
    /**
     * @var Model
     */
    private Model $model;
    /**
     * @var BuilderQuery
     */
    private BuilderQuery $buildQuery;

    /**
     * @var array $withDefault
     */
    private array $withDefault=[];

    /**
     * self constructor.
     * @param string $relationClass
     */
    public function __construct(string $relationClass)
    {
        $this->buildQuery = new BuilderQuery(new $relationClass);
    }

    public function withDefault(array $default):self
    {
        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->buildQuery->$name(...$arguments);
    }


    /**
     * @param Collection $model
     * @param int $primaryKey
     * @return Model|null
     */
    private function findHasRelation(Collection $model, int $primaryKey): ?Model
    {
        foreach ($model as $item) {
            if ((int)$item->{$this->foreignKey} === $primaryKey) {
                return $item;
            }
        }
        return null;
    }
    /**
     * @param array $records
     * @param string $relation
     * @return array
     */
    public function getWithRelation(array $records, string $relation): array
    {
    }

    /**
     * @param array $primaryKeyValues
     * @return Collection
     */
    private function getWithWhereIn(array $primaryKeyValues): Collection
    {
        return $this->buildQuery->whereIn($this->foreignKey, $primaryKeyValues)->get();
    }

    /**
     * @param string $foreignKey
     * @return self
     */
    public function setForeignKey(string $foreignKey): self
    {
        $this->foreignKey = $foreignKey;
        return $this;
    }

    /**
     * @return object|null
     */
    public function get()
    {
        $this->buildQuery->where($this->foreignKey, $this->model->getPrimaryKeyValue());
        return $this->buildQuery->first();
    }

    /**
     * @param string $primaryKey
     * @return self
     */
    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * @param Model $model
     * @return self
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return $this
     */
    public function build(): self
    {
        return $this;
    }
}
