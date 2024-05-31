<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Model;

final class TestModel {

    use MethodModelTrait;
    use CanBeDisabledTrait;
    use CanHaveTimeoutTrait;

    private ?string $dataProvider = null;

    public function withClass(string $class) : TestModel {
        $new = clone $this;
        $new->class = $class;
        return $new;
    }

    public function getDataProvider() : ?string {
        return $this->dataProvider;
    }

    public function setDataProvider(string $dataProvider) : void {
        $this->dataProvider = $dataProvider;
    }

}