<?php
namespace Catalyst\Tests\Command;

use Catalyst\Command\ResourcesCommand;
use Catalyst\Service\CatalystService;
use Symfony\Component\Console\Tester\CommandTester;

class ResourcesCommandTest extends CommandTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->initCommand(new ResourcesCommand(new CatalystService()));
    }

    public function testExecute()
    {
        chdir(__DIR__ . '/../../projects/GMLProject');
        $command = $this->application->find('resources');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName()
        ]);

        $output = $commandTester->getDisplay();

        $expectedGuids = [
            '0bb36c74-dc11-4a76-8ff5-0d89046b21bf', '119cbe88-3522-4eb0-9a65-67592f368412',
            '1980e77a-3df2-4b78-acdb-55b36295e218', '2c6daa16-0197-401d-bb95-207f2b2990e3',
            '2ed040a7-59f4-4c9c-ad9b-001f105af88a', '4dc59e0d-3c4d-4a62-9ba6-2c4e9fcd3eb5',
            '5214ea11-2cc7-4e91-acd4-136b41310eef', '58d409a0-91fb-4c73-a851-274797e5000c',
            '60dd397d-bc63-4ce0-90e9-c1c8ab08e8b7', '6fd70c5a-55a8-4c35-842d-5e0cb43993d6',
            '75ac291e-7061-4bcb-8e8a-3b3545332d41', '75e726a7-cf89-40a6-bfa5-64f058241ec7',
            '76c75b25-2e33-48eb-ae94-bfa7c1799780', '809833bc-8be7-46e6-a95b-d75f9a4ae2ec',
            '8427047f-9ef8-4c77-89f3-9c20623d07b6', '8a9c5eec-725f-485c-a371-5e24a2741193',
            '906d13b2-ac73-4237-9af8-4a24322647c9', '982c8bc9-bdab-4c71-b840-f77890b3ae9d',
            '98a7ede6-69c9-45f5-91ac-ccdd8db943eb', '98d50ca0-61c1-4538-9b7c-2a7c2ba66602',
            'a128950b-5063-4876-b4a6-b99dbd2ea6d1', 'a9188620-a624-4a5a-83ae-a1b53faf038b',
            'ab936476-fec6-43fe-894c-7c213b1f3b94', 'ab958c18-ec8c-4be4-9684-62713fbc0cae',
            'ae41f6aa-5862-414b-8738-99e04eab6356', 'b1ae34bc-a358-4ef9-af35-978fa1a8c21f',
            'b337607e-fe49-4e33-8428-d3728b9d9b23', 'b3b45f12-a0aa-47c3-8eaa-8396e6a8967d',
            'b3fc3dc3-03ac-4c19-83a5-b1ce9f50f1fa', 'c1ba437e-11e9-4da7-928b-a432fa73885c',
            'c3314a25-3fa0-4251-bd84-390475eb2c27', 'cc98d028-7bdd-4680-85f3-c87a7baa481e',
            'dc2198bd-3230-42f1-ae22-4df860543f85', 'dcb3b9aa-efa0-4fc9-b29c-cd1a5e1d39ea',
            'dcd740e7-91f4-4b74-89cb-8c77d91dae56', 'dd6a3339-5c46-41af-9c68-0eae5b436837',
            'e021792b-59ca-418e-a054-232fc8f68838', 'e3b52a1a-e925-4144-bd8e-191c7c32e021',
            'e42bf5cc-3f46-4d67-a6d0-a4885a11ac3f', 'e8f3e171-c9ef-4af7-9f68-151e2f5ba191',
            'ed726ca8-8ac5-4c4d-8bbe-37eadbf018fb', 'f418569b-3bdd-4706-a0e4-364317f54032',
            'f4ec481c-9aee-4d6b-92eb-bf29130bac47', 'fcb42a9e-cf5c-478f-aa06-8bcbe9e92af2',
            'ffc9fb66-f6b4-45ec-b163-aeed4b967285',
        ];

        $expectedNames = [
            'scripts', 'parents', 'sprites',
            'paths', 'options', 'room_testing', 'rooms', 'notes', 'spr_numbers', 'obj_using_simple_project',
            'HTML5', 'extensions', 'path_shape', 'ts_numbers', 'iOS', 'text_document.txt', 'note_inside_group',
            'group in children', 'empty group', 'timelines', 'Amazon Fire', 'Linux', 'sh_passthrough', 'objects',
            'obj_child_child', 'children', 'obj_parent', 'datafiles', 'fnt_arial_8', 'scr_multiply', 'Android',
            'shaders', 'sounds', 'Windows', 'configs', 'tilesets', 'tl_one', 'group with empty group', 'Note group',
            'Default', 'snd_explosion', 'macOS', 'fonts', 'obj_child', 'note_not_in_group', '~MAIN OPTIONS FILE~',
        ];

        foreach ($expectedGuids as $guid) {
            $this->assertStringContainsString($guid, $output);
        }

        foreach ($expectedNames as $name) {
            $this->assertStringContainsString($name, $output);
        }
    }
}
