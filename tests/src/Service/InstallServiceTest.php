<?php

namespace Catalyst\Tests\Service;

use Catalyst\Service\CatalystService;
use Catalyst\Service\InstallService;
use Catalyst\Service\PackageService;
use Catalyst\Service\StorageService;
use Mockery\MockInterface;

class InstallServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var InstallService */
    private $subject;

    /** @var PackageService|MockInterface */
    private $packageService;

    /** @var StorageService|MockInterface */
    private $storageService;

    protected function setUp() : void
    {
        $this->storageService = \Mockery::mock(StorageService::class . '[persist]');
        StorageService::setInstance($this->storageService);
        $this->packageService = new PackageService();
        $this->packageService->clearRepositories(); // Remove default online repositories
        $this->subject = new InstallService($this->packageService);
    }

    public function testProjectInstall()
    {
        // Load the actual file, then overwrite the storage service once files are going to be changed;
        $projectPath = __DIR__ . '/../../mocks/projects/20_IgnorableFiles';
        chdir($projectPath);
        $catalystProject = (new CatalystService())->load('.');

        // Actual testing
        $this->subject->install($catalystProject);

        $this->assertCount(95, $GLOBALS['storage']['writes']);
        $this->assertCount(0, $GLOBALS['storage']['deletes']);
        $this->assertCount(58, $catalystProject->gitIgnore());

        $filesThatShouldBeWritten = [
            'views/1df198a3-d383-6e4d-3315-50e5fdbe8737.yy',
            'views/407dcbed-d554-40f8-85af-eead7752aeb4.yy',
            'views/08fbae33-04e9-9969-c59b-ee2746e96bd2.yy',
            'views/ca37219c-b827-89e7-b48e-5f381879532a.yy',
            'sprites/spr_numbers/55f3e0fd-28e0-4673-8555-1eb43a7a3fcd.png',
            'sprites/spr_numbers/624bbd80-09ca-4c9d-a977-61b54baa2dac.png',
            'sprites/spr_numbers/81f2cbda-0b2b-45cf-9fbd-beced1b583cf.png',
            'sprites/spr_numbers/layers/55f3e0fd-28e0-4673-8555-1eb43a7a3fcd/1ce14796-9b17-4cb7-bf02-74f8475e41a1.png',
            'sprites/spr_numbers/layers/624bbd80-09ca-4c9d-a977-61b54baa2dac/1ce14796-9b17-4cb7-bf02-74f8475e41a1.png',
            'sprites/spr_numbers/layers/81f2cbda-0b2b-45cf-9fbd-beced1b583cf/1ce14796-9b17-4cb7-bf02-74f8475e41a1.png',
            'sprites/spr_numbers/spr_numbers.yy',
            'views/77c29ba4-2114-485b-7fb1-576ab2358726.yy',
            'views/878df58d-a95c-4aaa-ae30-69a4cc33ad75.yy',
            'views/3d853f66-41e2-4fc7-652c-7111e9399d2c.yy',
            'views/fa01aaf7-fae7-7e65-ebd8-46d1881c14b2.yy',
            'tilesets/ts_numbers/output_tileset.png',
            'tilesets/ts_numbers/ts_numbers.yy',
            'views/d73405d2-b733-743c-8376-039799af46aa.yy',
            'views/0308bfe6-e741-4462-a822-b51973aaf568.yy',
            'views/07ce940a-649e-ac4a-b158-33d338f4be8c.yy',
            'views/1788bf42-ae55-43d8-2304-dc33a4a1968c.yy',
            'sounds/snd_explosion/snd_explosion',
            'sounds/snd_explosion/snd_explosion.yy',
            'views/8d9b251a-8bf9-0bbb-67b1-86ecbc8e709b.yy',
            'views/a4cafe4b-ddd8-4cf9-9b98-49363c222b53.yy',
            'views/91ccf851-2d26-784d-c7e3-c7dd9ee0d107.yy',
            'views/463dee24-631a-f221-7e30-4e870008e1e6.yy',
            'paths/path_shape/path_shape.yy',
            'views/f7c27459-f01b-2208-c908-74c52eddfd69.yy',
            'views/02c768e4-f6b6-48b4-a2e4-98b847481b97.yy',
            'views/ce91c31d-e148-75bd-6607-ec77f6cb472f.yy',
            'views/72b89d30-c1a9-edfc-fb94-95711ba780ce.yy',
            'scripts/scr_multiply/scr_multiply.gml',
            'scripts/scr_multiply/scr_multiply.yy',
            'views/8aa61896-761c-e59b-7533-c47ffa2a9924.yy',
            'scripts/script_notgone_test3/script_notgone_test3.yy',
            'views/4ed83a4d-a2e3-42d3-480c-aa4eda9b3ede.yy',
            'views/2eba1da1-802b-4ca3-94a3-9e3e28bddf5c.yy',
            'views/43c4d5ae-cc83-9715-09ef-e1524be6020d.yy',
            'views/a5892283-3b46-2680-d704-baa64b050896.yy',
            'shaders/sh_passthrough/sh_passthrough.fsh',
            'shaders/sh_passthrough/sh_passthrough.vsh',
            'shaders/sh_passthrough/sh_passthrough.yy',
            'views/ba831898-f44f-82f3-83d0-5acbc22f222c.yy',
            'views/1661de33-c10c-47d4-8563-e9e6796ab2ae.yy',
            'views/5fca81fc-9352-b973-3739-c126a36493ed.yy',
            'views/fb2d8d4a-989e-1ebe-0cd3-29ee21326d96.yy',
            'fonts/fnt_arial_8/fnt_arial_8.old.png',
            'fonts/fnt_arial_8/fnt_arial_8.old.yy',
            'fonts/fnt_arial_8/fnt_arial_8.png',
            'fonts/fnt_arial_8/fnt_arial_8.yy',
            'views/37eff734-06c3-04df-57be-7c76e585fddd.yy',
            'views/80a02540-d0a1-41f2-a644-47cd8c8ee0a5.yy',
            'views/98d1198d-6d8a-2c13-b463-05b723143ba9.yy',
            'views/e4df55d3-5418-8208-2d95-019757e87c37.yy',
            'timelines/tl_one/moment_0.gml',
            'timelines/tl_one/moment_30.gml',
            'timelines/tl_one/tl_one.yy',
            'views/07a5c14a-1669-529d-15f9-07aca623ae5e.yy',
            'views/1f697a03-bb14-404b-9364-2aa049382c15.yy',
            'views/245f309f-257e-d866-e4dc-e99edf224623.yy',
            'views/08465e4c-e94b-351f-1d2b-5928208cb999.yy',
            'views/81dcc03c-cf4f-e5fb-a4e6-ac937cfe90db.yy',
            'objects/obj_parent/Create_0.gml',
            'objects/obj_parent/Draw_0.gml',
            'objects/obj_parent/obj_parent.yy',
            'views/f9161f58-09fc-a307-d176-e0a173c0ca4d.yy',
            'objects/obj_child/Create_0.gml',
            'objects/obj_child/Step_0.gml',
            'objects/obj_child/obj_child.yy',
            'views/08e5469d-5d4d-f20d-3369-892a4c7dc40b.yy',
            'objects/obj_child_child/Create_0.gml',
            'objects/obj_child_child/Step_0.gml',
            'objects/obj_child_child/obj_child_child.yy',
            'objects/obj_using_simple_project/obj_using_simple_project.yy',
            'views/be31e806-5518-2f9d-b7cb-8ef38311e23e.yy',
            'objects/testing_shouldbe_here_test/testing_shouldbe_here_test.yy',
            'objects/thisobjectshouldnotbegone_test3/thisobjectshouldnotbegone_test3.yy',
            'views/9e33827b-4dfb-a705-9d51-1b9855b9f63f.yy',
            'views/dc0b760b-c9f3-440a-971e-e4c2a683c395.yy',
            'views/a8069179-0018-8bd0-04c9-0958170a3961.yy',
            'views/d1230e78-16e2-1b55-ed2d-763a05e86189.yy',
            'rooms/room_testing/room_testing.yy',
            'views/d75f0b2a-de02-ab0c-95f4-cabc519c6587.yy',
            'views/4e2e3fde-6384-460f-87b7-66e6e85c8fe4.yy',
            'views/320ff77b-893f-b973-824c-753abc34b9f2.yy',
            'views/98666536-cf8e-6e49-a136-390d72bee003.yy',
            'views/ca0b843b-9b15-de33-8d56-06c2f0e3326a.yy',
            'notes/note_inside_group.txt',
            'notes/note_inside_group.yy',
            'notes/note_not_in_group.txt',
            'notes/note_not_in_group.yy',
            'datafiles_yy/text_document.txt.yy',
            'datafiles/text_document.txt',
            'views/454e7cf0-c931-47fe-a2ad-d08fa830bd21.yy',
        ];

        $filesThatShouldNotBeWritten = [
            'sprites/spr_to_ignore/bc3ca4da-3afb-4fb9-9ed0-5353691cc828.png',
            'sprites/spr_to_ignore/layers/bc3ca4da-3afb-4fb9-9ed0-5353691cc828/320be551-5d29-4ad9-9b4a-77d1851f6a89.png',
            'sprites/spr_to_ignore/spr_to_ignore.yy',
            'views/20db2545-3279-3669-3752-3a0ffcbce589.yy',
            'scripts/scr_in_group_to_ignore/scr_in_group_to_ignore.yy',
            'scripts/test_script1/test_script1.yy',
            'scripts/script_gone_test2/script_gone_test2.yy',
            'objects/test_object1/Step_0.gml',
            'objects/test_object1/test_object1.yy',
            'objects/testing_shouldbe_gone_test2/testing_shouldbe_gone_test2.yy',
            'views/a1784ccf-6f73-9a6c-09b0-c79ae43743f7.yy',
            'objects/thisobjectshouldbegone/thisobjectshouldbegone.yy',

        ];

        foreach ($filesThatShouldBeWritten as $file) {
            $this->assertArrayHasKey(
                StorageService::pathToAbsolute($file),
                $GLOBALS['storage']['writes']
            );
        }

        foreach ($filesThatShouldNotBeWritten as $file) {
            $this->assertArrayNotHasKey(
                StorageService::pathToAbsolute($file),
                $GLOBALS['storage']['writes']
            );
        }
    }

    protected function tearDown() : void {
        StorageService::reset();
    }

}