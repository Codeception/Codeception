<?php

class MessageCest
{
    public function allMessages(MessageGuy $I)
    {
        $this->showMessage($I, 1);
        $this->showMessage($I, 2);
        $this->showMessage($I, 3);
        $this->showMessage($I, 4);
    }

    public function message2(MessageGuy $I)
    {
        $this->showMessage($I, 2);
    }

    protected function showMessage(MessageGuy $I, $num)
    {
        $I->expect('message'.$num.': ' . $I->getMessage('message'.$num));
    }

    /**
     *
     * @env env2,env1
     *
     * @param MessageGuy $I
     */
    public function multipleEnvRequired(MessageGuy $I)
    {
        $I->expect('Multiple env given');
    }

}
