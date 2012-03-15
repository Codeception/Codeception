<?

namespace Codeception\Module;

class Kohana extends \Codeception\Util\Framework implements \Codeception\Util\FrameworkInterface {

	public function _initialize() {
		
	}

	public function _before(\Codeception\TestCase $test) {
		$this->client = new \Codeception\Util\Connector\Kohana();
		$this->client->setIndex('public/index.php');
	}

	public function _after(\Codeception\TestCase $test) {
		$_SESSION = array();
		$_GET = array();
		$_POST = array();
		$_COOKIE = array();
		parent::_after($test);
	}

}