<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyCheck
{
    public function run()
    {
        $data = [];
        $otherData = [];

        if (count($data)) {

        } elseif (count($otherData)) {

        }
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\Fixture;

class FixtureMarkAsTruthyCheck
{
    public function run()
    {
        $data = [];
        $otherData = [];

        if ($data !== []) {

        } elseif ($otherData !== []) {

        }
    }
}

?>
