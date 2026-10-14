<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/** Catch incorrect table/foreign-key mappings across all declared model relations. */
class EloquentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_declared_relations_can_query_the_migrated_schema(): void
    {
        foreach (glob(app_path('Modules/*/Models/*.php')) as $file) {
            $domain = basename(dirname($file, 2));
            $class = 'App\\Modules\\'.$domain.'\\Models\\'.basename($file, '.php');
            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract() || !$reflection->isSubclassOf(Model::class)) {
                continue;
            }
            $model = new $class;
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $class || $method->getNumberOfRequiredParameters() !== 0) {
                    continue;
                }
                $type = $method->getReturnType();
                if (!$type instanceof \ReflectionNamedType || !is_a($type->getName(), Relation::class, true)) {
                    continue;
                }
                $relation = $method->invoke($model);
                $this->assertInstanceOf(Relation::class, $relation);
                // Executing the SQL catches wrong table names and foreign-key columns.
                $relation->limit(1)->get();
            }
        }
    }
}
