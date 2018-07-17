function str_repeat(x, n) {
    var r = '';
    while (n > 0) {
        r = r + x;
        n = n - 1;
    }
    return r;
}

function pad(num, size) {
    var s = "000000000" + num;
    return s.substr(s.length-size);
}

function substr_count (haystack, needle, offset, length) { // eslint-disable-line camelcase
  //  discuss at: http://locutus.io/php/substr_count/
  // original by: Kevin van Zonneveld (http://kvz.io)
  // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Thomas
  //   example 1: substr_count('Kevin van Zonneveld', 'e')
  //   returns 1: 3
  //   example 2: substr_count('Kevin van Zonneveld', 'K', 1)
  //   returns 2: 0
  //   example 3: substr_count('Kevin van Zonneveld', 'Z', 0, 10)
  //   returns 3: false

  var cnt = 0;

  haystack += '';
  needle += '';
  if (isNaN(offset)) {
    offset = 0;
  }
  if (isNaN(length)) {
    length = 0;
  }
  if (needle.length === 0) {
    return false;
  }
  offset--;

  while ((offset = haystack.indexOf(needle, offset + 1)) !== -1) {
    if (length > 0 && (offset + needle.length) > length) {
      return false;
    }
    cnt++;
  }

  return cnt;
}

function check_match(guess, compare_a, compare_b, possible, duplicate) {
    var a = 0;
    var b = 0;
    var dups = [];
    var ret = '';
    for (var i = 0; i < possible.length; i++) {
        if (possible.indexOf(guess.substr(i, 1)) >= 0) {
            var current_digit = guess.substr(i, 1);
            if (current_digit == possible.substr(i, 1)) {
                ret = 'A';
            } else {
                ret = 'B';
            }
        } else {
            ret = 'C';
        }

        if (typeof current_digit !== 'undefined') {
            if (typeof dups[current_digit] !== 'undefined') {
                if ((dups[current_digit] == 'B') && (ret == 'A')) {
                    b = b - 1;
                    a = a + 1;
                    dups[current_digit] = ret;
                }
            } else {
                if (ret == 'A') {
                    a = a + 1;
                    dups[current_digit] = ret;
                } else if (ret == 'B') {
                    b = b + 1;
                    dups[current_digit] = ret;
                }
            }
        }
        delete current_digit;


    }

    if ((a == parseInt(compare_a)) && (b == parseInt(compare_b))) {
        return true;
    } else {
        return false;
    }

}


function check_dups(x) {
    var digits = '0123456789';
    var check = false;
    for (var i = 0; i < 10; i++) {
        var p = x.substr(i, 1);
        if (substr_count(x, p) > 1) {
            check = true;
            break;
        }
    }
    return check;
}

function calc_nums() {
    if($('#lbl_msg').html() != '計算中') {
        $('#lbl_msg').html('計算中');
        var ret = '';
        var patt = new RegExp('^[0-9]+$');

        // 檢查有填的數字和回應是否完整
        for (var i = 1; i <= 20; i++) {

            var txt_guess = $('#guess_' + i).val();
            var txt_a = $('#a_' + i).val();
            var txt_b = $('#b_' + i).val();

            if ((i > 1) && (txt_guess != '')) {
                //檢查填的數字長度是否都相同
                if (txt_guess.length != $('#guess_1').val().length) {
                    ret = '第' + i + '列數字長度不正確';
                    break;
                }
            }

            if ((txt_guess != '') && (txt_a != '') && (txt_b != '')) {
                //檢查是否都填數字
                if (!(patt.test(txt_guess) && patt.test(txt_a) && patt.test(txt_b))) {
                    ret = '第' + i + '列輸入了非數字字元';
                    break;
                }
                //檢查A跟B的總和是否超過數字長度
                if ((parseInt(txt_a) + parseInt(txt_b)) > txt_guess.length) {
                    ret = '第' + i + '列輸入資料長度有誤';
                    break;
                }
            } else if ((txt_guess == '') && (txt_a == '') && (txt_b == '')) {
                //skip
            } else {
                ret = '第' + i + '列輸入資料不完整';
                break;
            }

        }

        if (ret == '') {
            //初始化數字資料
            var dups = $('#chk_dups').prop('checked');
            var digits = $('#guess_1').val().length;
            var step = str_repeat('0', digits);
            var goal = str_repeat('9', digits);
            var possible = [];

            do {
                var add_this = false;
                if ((dups == false) && (check_dups(step) == false)) {
                    add_this = true;
                }
                if (dups == true) {
                    add_this = true;
                }
                if (add_this) {
                    possible.push(step);
                }
                if (step == goal) {
                    break;
                }
                step = pad(parseInt(step) + 1, digits);
            } while (1 == 1);

            for (var i = 1; i <= 20; i++) {
                //排除不可能的數字
                var txt_guess = $('#guess_' + i).val();
                var txt_a = $('#a_' + i).val();
                var txt_b = $('#b_' + i).val();

                if (txt_guess != '') {

                    var new_possible = [];
                    for (var j = 0; j < possible.length; j++) {
                        if (check_match(txt_guess, txt_a, txt_b, possible[j], dups)) {
                            new_possible.push(possible[j]);
                        }
                    }

                    possible = new_possible;
                }
            }

            //列出可能的答案
            if (possible.length > 1) {
                ret = '目前可能有' + possible.length + '種答案\n';
                if (possible.length > 200) {
                    ret = ret + '可能的答案太多了，暫不一一列舉';
                } else {
                    for (var k = 0; k < possible.length; k++) {
                        ret = ret + possible[k] + ' ';
                    }
                }
            } else if (possible.length == 1) {
                ret = '答案為' + possible[0];
            } else {
                ret = '輸入有問題導致算不出答案';
            }

        }



        $('#result').val(ret);
        $('#lbl_msg').html('等候輸入');
        return true;
    } else {
        return false;
    }
}

$("input[id^='guess_']").keyup(function() {
    if (!calc_nums()) {
        setTimeout(function() {
            calc_nums();
        }, 2000);
    }
});

$("input[id^='a_']").keyup(function() {
    if (!calc_nums()) {
        setTimeout(function() {
            calc_nums();
        }, 2000);
    }
});

$("input[id^='b_']").keyup(function() {
    if (!calc_nums()) {
        setTimeout(function() {
            calc_nums();
        }, 2000);
    }
});

$("#btn_cal").click(function() {
    calc_nums();
});

$("#btn_clear").click(function() {
    $('input').val('');
    $('#guess_1').focus();
});

$(document).ready(function() {
});
