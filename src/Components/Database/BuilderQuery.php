<?php

namespace App\Components\Database;

use App\Components\Collection\Collection;
use PDO;

/**
 * Class BuilderQuery
 * @package App\Database
 */
class BuilderQuery
{
    /**
     * @var Model
     */
    private Model $modelInstance;

    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * @var string|null
     */
    private ?string $query = null;


    /**
     * @var array
     */
    private array $bindArray = [];

    /**
     * @var string|null
     */
    private ?string $orderBy = null;

    /**
     * @var bool
     */
    private bool $isWhereUsed = false;

    /**
     * @var string $whereQuery
     */
    private ?string  $whereQuery = null;

    /**
     * @var
     */
    private $limit;


    /**
     * BuilderQuery constructor.
     * @param Model $model
     * @param PDO $pdo
     */
    public function __construct(Model $model, PDO $pdo)
    {
        $this->modelInstance = $model;
        $this->pdo = $pdo;
    }


    /**
     * @param $key
     * @param $operator
     * @param $value
     * @return $this
     */
    public function where($key, $value, $operator = '='): self
    {
        if ($this->isWhereUsed()) {
            $this->whereQuery .= " AND $key$operator:where_$key";
        } else {
            $this->whereQuery = " WHERE $key$operator:where_$key ";
        }

        $this->bindArray[":where_$key"] = $this->addSingleQuotation($value);
        $this->isWhereUsed = true;
        return $this;
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     * @return $this
     * @noinspection PhpUnused
     */
    public function orWhere($key, $value, $operator = '='): self
    {
        if ($this->isWhereUsed()) {
            $this->whereQuery .= " OR $key$operator:or_where$key";
        } else {
            $this->whereQuery = " WHERE $key$operator:or_where$key ";
        }

        $this->bindArray[":or_where$key"] = is_string($value) ? "'$value'" : $value;
        $this->isWhereUsed = true;
        return $this;
    }

    /**
     * @param $data
     * @return string
     */
    private function addSingleQuotation($data): string
    {
        return is_string($data) ? "'$data'" : $data;
    }

    /**
     * @param string $column
     * @param $smallValue
     * @param $bigValue
     * @param $type  @explain between or  not between
     * @return $this
     */
    private function builderBetween(string $column, $smallValue, $bigValue, $type): self
    {
        $smallValue = $this->addSingleQuotation($smallValue);
        $bigValue = $this->addSingleQuotation($bigValue);
        if ($this->isWhereUsed()) {
            $this->whereQuery .= " AND $column  $type :between_small_value$column AND :between_big_value$column";
        } else {
            $this->whereQuery = " WHERE $column $type $smallValue AND $bigValue ";
        }
        $this->bindArray[":between_small_value$column"] = $smallValue;
        $this->bindArray[":between_big_value$column"] = $bigValue;

        $this->isWhereUsed = true;
        return $this;
    }

    /**
     * @param string $column
     * @param $smallValue
     * @param $bigValue
     * @return $this
     */
    public function between(string $column, $smallValue, $bigValue): self
    {
        return $this->builderBetween($column, $smallValue, $bigValue, 'BETWEEN');
    }

    /**
     * @param string $column
     * @param $smallValue
     * @param $bigValue
     * @return $this
     */
    public function notBetween(string $column, $smallValue, $bigValue): self
    {
        return $this->builderBetween($column, $smallValue, $bigValue, 'NOT BETWEEN');
    }

    /**
     * @return array
     */
    public function getBindArray(): array
    {
        return $this->bindArray;
    }


    /**
     * @param array $bindArray
     * @return BuilderQuery
     */
    private function setBindArray(array $bindArray): BuilderQuery
    {
        $this->bindArray = $bindArray;
        return $this;
    }

    /**
     * @return string
     */
    private function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     * @return BuilderQuery
     */
    private function setOrderBy(string $orderBy): BuilderQuery
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * @return Model
     */
    private function getModelInstance(): Model
    {
        return $this->modelInstance;
    }

    /**
     * @return mixed|string
     */
    private function getTable()
    {
        return $this->modelInstance->getTable();
    }

    /**
     * @param $columns
     * @return string
     */
    private function selectBuilderQuery($columns): string
    {
        $columns=implode(',',$columns) ?: '*';
        return "SELECT $columns FROM {$this->getTable()}{$this->getWhereQuery()}{$this->getOrderBy()}{$this->getLimit()}";
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function get(...$columns): Collection
    {
        $this->setQuery($this->selectBuilderQuery($columns));
        return $this->run();
    }


    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        $this->setQuery("select * from {$this->getTable()} where {$this->modelInstance->getPrimaryKey()}=:id");
        $this->setBindArray([$this->modelInstance->getPrimaryKey() => $id]);

        return $this->fetch();
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function findOrFail($id)
    {
        return $this->find($id) ?: die(view(404));
    }

    /**
     * @return Collection
     */
    private function run(): Collection
    {
        $result = $this->fetchAll();

        if ($this->modelInstance->getHidden()) {
            $this->unsetHiddenPropertiesFromArray($result);
        }
        return new Collection($result);
    }


    /**
     * @param object $model
     * @return object
     */
    private function unsetHiddenProperties(object $model): object
    {
        foreach ($this->getModelInstance()->getHidden() as $hidden) {
            unset($model->$hidden);
        }
        return $model;
    }

    /**
     * @param $result
     */
    private function unsetHiddenPropertiesFromArray($result): void
    {
        $hidden = $this->modelInstance->getHidden();
        array_map(static function ($object) use ($hidden) {
            if ($hidden) {
                foreach ($hidden as $property) {
                    unset($object->$property);
                }
            }
        }, $result);
    }

    /**
     * @param string $column
     * @return $this
     */
    public function orderByAsc(string $column = 'id'): self
    {
        $this->setOrderBy(" ORDER BY $column ASC ");
        return $this;
    }

    /**
     * @param string $column
     * @return $this
     */
    public function orderByDesc(string $column = 'id'): self
    {
        $this->setOrderBy(" ORDER BY $column DESC ");
        return $this;
    }

    /**
     * @param mixed $query
     * @return BuilderQuery
     */
    private function setQuery($query): BuilderQuery
    {
        $this->query = $query;
        return $this;
    }


    /**
     * @param array $columns
     * @return object
     */
    public function first(...$columns): ?object
    {
        $this->setQuery($this->selectBuilderQuery($columns));
        if($this->fetch()) {
            return $this->unsetHiddenProperties($this->fetch());
        }
        return null;
    }

    /**
     * @param mixed ...$columns
     * @return object|void|null
     */
    public function firstOrFail(...$columns)
    {
        return $this->first(...$columns) ?: die(view('404'));
    }

    /**
     * @param array $columns
     * @return object
     */
    public function last(...$columns): ?object
    {
        $this->setOrderBy(" ORDER BY {$this->modelInstance->getPrimaryKey()} DESC ");
        $this->limit(1);
        $this->setQuery($this->selectBuilderQuery($columns));
        if($this->fetch()) {
            return $this->unsetHiddenProperties($this->fetch());
        }
        return null;
    }

    /**
     * @param mixed ...$columns
     * @return object|void|null
     */
    public function lastOrFail(...$columns)
    {
        return $this->last(...$columns) ?: die(view('404'));
    }
    /**
     * @return mixed
     */
    private function getQuery()
    {
        return $this->query;
    }


    /**
     * @return array
     */
    public function save()
    {
        return get_object_vars($this->modelInstance);
    }


    /**
     * @return FetchStatement
     */
    private function builderFetchStatement(): FetchStatement
    {
        return (new FetchStatement())
            ->setBuilderQuery($this)
            ->setModelClass($this->modelInstance->getTable())
            ->setPdo($this->pdo)
            ->setModelClass($this->modelInstance->getStaticClass())
            ->setQuery($this->getQuery())
            ->setBindArray($this->getBindArray());
    }

    /**
     * @return object|null
     */
    public function fetch()
    {
        return $this->builderFetchStatement()->fetch();
    }

    /**
     * @return array|null
     */
    public function fetchAll(): ?array
    {
        return $this->builderFetchStatement()->fetchAll();
    }

    /**
     * @return bool
     */
    private function isWhereUsed(): bool
    {
        return $this->isWhereUsed;
    }

    /**
     * @return string
     */
    public function getWhereQuery(): ?string
    {
        return $this->whereQuery;
    }

    /**
     * @param $limit
     * @param null $end
     * @return $this
     */
    public function limit($limit, $end = null): self
    {
        if ($end === null) {
            $this->limit = " LIMIT $limit ";
        } else {
            $this->limit = " LIMIT $limit,$end ";
        }
        return $this;
    }

    /**
     * @return mixed
     */
    private function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $name = sprintf('scope%s', ucfirst($name));
        if (method_exists($this->modelInstance, $name)) {
            return $this->modelInstance->$name($this);
        }
        return $this;
    }
}
