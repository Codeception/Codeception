<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Cept {

    protected $template  = <<<EOF
<?php {{use}}
\$I = new {{guy}}(\$scenario);
\$I->wantTo('perform actions and see result');

EOF;

    protected $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function produce()
    {
        $guy = $this->settings['class_name'];
        $use = $this->settings['namespace']
            ? "use {$this->settings['namespace']}\\$guy;"
            : '';

        return (new Template($this->template))
            ->place('guy', $guy)
            ->place('use', $use)
            ->produce();
    }
    
} 