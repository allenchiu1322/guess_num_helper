<?php

class guess_num {

    public $config;

    public $digits; //題目有幾位數
    public $dups;   //是否數字有重複

    public $possible; //可能是答案的數字
    public $guesses = []; //猜過的數字和提示

    public $list_under = 200; //此數字以下才列舉出可能答案

    function __construct() {

    }

    public function ansi_color($msg, $hl = '0', $fg = '7', $bg = '0') {
        $ret = chr(27) . sprintf('[%d;3%d;4%dm', $hl, $fg, $bg) . $msg . chr(27). '[0m';
        return $ret;
    }

    public function out($msg) {
        echo($msg . PHP_EOL);
    }

    public function ask_a_string($prompt, $verify = FALSE) {
        if ($verify) {
            $correct = '';
            while ($correct <> 'Y') {
                echo($prompt);
                $stdin = fopen('php://stdin', 'r');
                $response = fgets($stdin);
                $correct = strtoupper($this->ask_a_string(sprintf('--> %s <-- 是否輸入正確? [Y/N]', trim($response))));
            }
            return trim($response);

        } else {
            echo($prompt);
            $stdin = fopen('php://stdin', 'r');
            $response = fgets($stdin);
            return trim($response);
        }
    }

    public function run_external_cmd($cmd) {
        system($cmd);
    }

    public function regexp_match($regexp, $string) {
        preg_match_all($regexp, $string, $matches);
        if (isset($matches[0][0]) && ($string == $matches[0][0])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function check_dups($num) {
        $digits = '0123456789';
        $check = FALSE;
        for ($i = 0; $i < 10; $i++) {
            if (substr_count($num, substr($digits, $i, 1)) > 1) {
                $check = TRUE;
                break;
            }
        }
        return $check;
    }

    public function init_possible() {
        $step = str_repeat('0', $this->digits);
        $goal = str_repeat('9', $this->digits);
        do {
            //列舉數字
            $add_this = FALSE;
            if (($this->dups == 'N' && ($this->check_dups($step) == FALSE))) {
                $add_this = TRUE;
            }
            if ($this->dups == 'Y') {
                $add_this = TRUE;
            }
            if ($add_this) {
                $this->possible[] = sprintf('%0' . $this->digits . 'd', $step);
            }
            //跳出
            if ($step == $goal) {
                break;
            }
            //數字+1
            $step = strval(intval($step) + 1);
        } while (TRUE);
    }

    public function check_match($guess, $compare_a, $compare_b, $possible) {
        $a = 0;
        $b = 0;
        $dups = [];
        for ($i = 0; $i < strlen($possible); $i++) {
            //先偵測答題是屬於A或B
            if (strpos($possible, substr($guess, $i, 1)) !== FALSE) {
                $current_digit = substr($guess, $i, 1);
                if ($current_digit == substr($possible, $i, 1)) {
                    $ret = 'A';
                } else {
                    $ret = 'B';
                }
            } else {
                $ret = 'C';
            }
            //處理數字重覆狀況
            if (isset($current_digit)) {
                if (isset($dups[$current_digit])) {
                    if (($dups[$current_digit] == 'B') && ($ret == 'A')) {
                        $b--;
                        $a++;
                        $dups[$current_digit] = $ret;
                    }
                } else {
                    if ($ret == 'A') {
                        $a++;
                        $dups[$current_digit] = $ret;
                    } else if ($ret == 'B') {
                        $b++;
                        $dups[$current_digit] = $ret;
                    }
                }
            }
            unset($current_digit);
            /*
            if ($ret == 'A') {
                $a++;
            } else if ($ret == 'B') {
                $b++;
            }
             */

        }
        if (($a == intval($compare_a)) && ($b == intval($compare_b))) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function apply_guess() {
        $new = [];
        foreach ($this->possible as $p) {
            $g = end($this->guesses);
            if ($this->check_match($g['guess'], $g['a'], $g['b'], $p)) {
                $new[] = $p;
            }
        }
        $this->possible = $new;
    }

    public function pretty_array($a) {
        $ret = '';
        foreach($a as $b) {
            $ret .= $b . ' ';
        }
        return $ret;
    }

    public function main() {
        //詢問題目有幾位數
        $this->digits = 'a';
        while(($this->regexp_match('/[0-9]+/', $this->digits) &&
              (intval($this->digits) < 11)) == FALSE) {
            $this->digits = $this->ask_a_string('題目有幾位數？', FALSE);
        }
        //詢問數字是否重覆
        $dup_check = 'a';
        while($this->regexp_match('/[NnYy]/', $dup_check) == FALSE) {
            $dup_check = $this->ask_a_string('題目數字是否重複？[y/N]', FALSE);
            if ($dup_check == '') {
                $dup_check = 'N';
                break;
            }
        }
        $this->dups = strtoupper($dup_check);

        //初始化可能的答案
        $orz = $this->init_possible();

        while (sizeof($this->possible) > 1) {
            $this->out(sprintf('第 %d 次猜測，目前可能有 %d 種答案', sizeof($this->guesses) + 1, sizeof($this->possible)));
            if (sizeof($this->possible) > $this->list_under) {
                $this->out('可能的答案太多了，暫不一一列舉');
            } else {
                $this->out($this->pretty_array($this->possible));
            }
            //詢問上一次回答的數字
            $check = FALSE;
            while ($check != TRUE) {
                $check = TRUE;
                $your_guess = $this->ask_a_string('上一次的回答: ', FALSE);
                //檢查是否輸入數字
                if (!$this->regexp_match('/[0-9]+/', $your_guess)) {
                    $this->out('輸入並非有效數字');
                    $check = FALSE;
                    continue;
                }
                //檢查數字長度
                if (strlen($your_guess) <> intval($this->digits)) {
                    $this->out('數字長度不對');
                    $check = FALSE;
                    continue;
                }
                //如果題目不重複，檢查數字有沒有重複
                if (($dup_check == 'N') && ($this->check_dups($your_guess) == TRUE)) {
                    $this->out('數字重複了');
                    $check = FALSE;
                    continue;
                }
            }
            //詢問出題者回覆的結果
            $a = (-1);
            $b = (-1);
            while (($a < 0) || ($b < 0)) {
                $ans_a = $this->ask_a_string('幾A?', FALSE);
                $ans_b = $this->ask_a_string('幾B?', FALSE);
                //檢查是否輸入數字
                if (!$this->regexp_match('/[0-9]+/', $ans_a)) {
                    $this->out('輸入並非有效數字');
                    $check = FALSE;
                    continue;
                }
                if (!$this->regexp_match('/[0-9]+/', $ans_b)) {
                    $this->out('輸入並非有效數字');
                    $check = FALSE;
                    continue;
                }
                //檢查A+B是否超出答案長度
                if ((intval($ans_a) + intval($ans_b)) > $this->digits) {
                    $this->out('輸入數字超出答案長度');
                    $check = FALSE;
                    continue;
                }
                $a = $ans_a;
                $b = $ans_b;
            }
            //記錄猜測和回應的組合
            $this_guess = [
                'guess' => $your_guess,
                'a' => $a,
                'b' => $b,
            ];
            $this->guesses[] = $this_guess;
            //列舉可能的答案
            $this->apply_guess();
        }
        if (sizeof($this->possible) == 1) {
            $this->out(sprintf('答案為 %s', $this->pretty_array($this->possible)));
        } else {
            $this->out('輸入有問題導致算不出答案');
        }
    }
}

$a = new guess_num;
$a->main();

