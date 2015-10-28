<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace fzed51\TestSuite;

/**
 * Description of Test
 *
 * @author fabien.sanchez
 */
class Test
{

    protected $class_test = '';
    private $nbSuccess_c = 0;
    private $nbTest_c = 0;
    private $nbSuccess_m = 0;
    private $nbTest_m = 0;
    private $lastCommentMethode = '';

    const ICO_OK = "<svg width=\"20\" height=\"20\"><g><circle fill=\"#00ff00\" stroke-width=\"40\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"4\" cx=\"10\" cy=\"10\" r=\"8\"/><path fill=\"none\" stroke=\"#7eff7e\" stroke-width=\"1\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"4\" d=\"m4 11a6 6 0 0 1 2-6 6 6 0 0 1 6-2\"/><path fill=\"none\" fill-rule=\"evenodd\" stroke=\"#005700\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"4\" d=\"m14 6c-2 2-4 5-5 8l-3-3\" /></g></svg>";
    const ICO_KO = "<svg width=\"20\" height=\"20\"><circle fill=\"#ff0000\" stroke-width=\"40\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"4\" cx=\"10\" cy=\"10\" r=\"8\"/><path fill=\"none\" stroke=\"#ff7e7e\" stroke-width=\"1\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"4\" d=\"m4 11a6 6 0 0 1 2-6 6 6 0 0 1 6-2\"/><path fill=\"none\" fill-rule=\"evenodd\" stroke=\"#ffffff\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"4\" d=\"m10 5l0 7\"/><circle fill=\"#ffffff\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"4\" r=\"1\" cy=\"15\" cx=\"10\"/></svg>";

    function __construct()
    {
        $this->class_test = get_class($this);
    }

    private function startTestClass()
    {
        $this->nbSuccess_c = 0;
        $this->nbTest_c = 0;
        $this->nbSuccess_m = 0;
        $this->nbTest_m = 0;
        $this->lastCommentMethode = '';
    }

    private function startTestMethode()
    {
        $this->nbSuccess_m = 0;
        $this->nbTest_m = 0;
        $this->lastCommentMethode = '';
    }

    private function getPourcentSuccessMethode()
    {
        if ($this->nbTest_m == 0) {
            return "****%";
        } else {
            return round(100 * $this->nbSuccess_m / $this->nbTest_m, 1) . "%";
        }
    }

    private function getPourcentSuccessClass()
    {
        if ($this->nbTest_c == 0) {
            return "****%";
        } else {
            return round(100 * $this->nbSuccess_c / $this->nbTest_c, 1) . "%";
        }
    }

    private function registerResult($result)
    {
        $this->nbTest_c++;
        $this->nbTest_m++;
        if ($result) {
            $this->nbSuccess_c++;
            $this->nbSuccess_m++;
        }
    }

    private function testMethode($methodeName)
    {
        $this->startTestMethode();
        call_user_func(array($this, $methodeName));
    }

    private function addComment($comment)
    {
        if ($this->lastCommentMethode <> '') {
            $this->lastCommentMethode .= "\n";
        }
        $this->lastCommentMethode .= $comment;
    }

    private function testMethodeIsSuccess()
    {
        return $this->nbSuccess_m == $this->nbTest_m;
    }

    private function getComment()
    {
        return $this->lastCommentMethode;
    }

    function run()
    {
        $this->startTestClass();
        ?>
        <div class="test_class">
            <h3>Test de la class <span class="class_name"><?= $this->class_test; ?></span></h3>
            <?php
            $methodes = get_class_methods($this->class_test);
            foreach ($methodes as $methode) {
                if (substr($methode, 0, 6) == 'test__') {
                    $methode_name = substr($methode, 6);
                    $this->testMethode($methode);
                    ?>
                    <div class="test_methode <?= ($this->testMethodeIsSuccess()) ? "ok" : "ko"; ?>">
                        <div class="test_methode_resume ihiddable"><?= $this->getPourcentSuccessMethode(); ?></div>
                        <p>Test de la methode <span class="methode_name"><?= $methode_name; ?></span></p>
                        <div class="test_methode_comment hiddable"><?= $this->getComment(); ?></div>
                    </div>
                <?php
            }
        }
        ?>
            <div class="test_class_resume"><?= $this->getPourcentSuccessClass(); ?></div>
        </div>
        <?php
    }

    function show_var($name)
    {
        global $$name;
        ob_start();
        var_dump($$name);
        $contents = ob_get_contents();
        ob_end_clean();
        echo "<div>Variable \$$name : $contents</div>";
    }

    function testEgal($elementTest, $elementComparaison, $libelle)
    {
        $success = ($elementTest == $elementComparaison);
        if ($success) {
            $this->addComment("<p>" . self::ICO_OK . nl2br($libelle) . "</p>");
        } else {
            $message = "<p>" . self::ICO_KO . nl2br($libelle) . '<br><em>';
            $message .= 'La valeur attendu est : ';
            $message .= '<tt>' . htmlentities((string) $elementComparaison) . '</tt><br>';
            $message .= 'la valeur testé est : ';
            $message .= '<tt>' . htmlentities((string) $elementTest) . '</tt>';
            $message .= '</em></p>';

            $this->addComment($message);
        }
        $this->registerResult($success);
    }

    function testThrowException(callable $callback, $Exception, $libelle, $param_arr = [])
    {
        $success = false;
        $ReturnException = '';
        try {
            call_user_func_array($callback, $param_arr);
        } catch (\Exception $ex) {
            $ReturnException = get_class($ex);
            $success = strcmp($Exception, $ReturnException) === 0;
        }
        if ($success) {
            $this->addComment("<p>" . self::ICO_OK . nl2br($libelle) . "</p>");
        } else {
            $message = "<p>" . self::ICO_KO . nl2br($libelle) . '<br><em>';
            $message .= 'L\'exception attendu est : ';
            $message .= '<tt>' . htmlentities((string) $Exception) . '</tt><br>';
            if ($ReturnException == '') {
                $message .= 'l\'élément testé ne renvoie pas d\'exception';
                $message .= '</em></p>';
            } else {
                $message .= 'l\'exception retournée est : ';
                $message .= '<tt>' . htmlentities($ReturnException) . '</tt>';
                $message .= '</em></p>';
            }

            $this->addComment($message);
        }
        $this->registerResult($success);
    }

}
