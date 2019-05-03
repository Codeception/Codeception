 <?php

 class DebugCest
 {
     public function testSomethingWithDebugFlag(DebugGuy $I)
     {
         $I->doAnAwesomeActionWithDebugFlag();
     }

     public function testSomethingWithoutDebugFlag(DebugGuy $I)
     {
         $I->doAnAwesomeActionWithoutDebugFlag();
     }

 }