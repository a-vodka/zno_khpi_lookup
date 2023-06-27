
function init_khpi_zno_lookup() {

    $ = jQuery;

    let zno_list = [];

    zno_list['UkrainianLanguage'] = 'Українська мова та література';
    zno_list['Math'] = 'Математика';
    zno_list['Physics'] = 'Фізика';

    zno_list['HistoryOfUkraine'] = 'Історія України';
    zno_list['ForeignLanguage'] = 'Іноземна мова';
    //zno_list['Geography'] = 'Географія';

    zno_list['Chemistry'] = 'Хімія';
    zno_list['Biology'] = 'Біологія';
    //zno_list['TK'] = 'Творчий конкурс';

    let zno_list_chk = [];

    zno_list_chk['UkrainianLanguage'] = true;
    zno_list_chk['Math'] = false;
    zno_list_chk['Physics'] = false;

    zno_list_chk['HistoryOfUkraine'] = false;
    zno_list_chk['ForeignLanguage'] = false;
    zno_list_chk['Geography'] = false;

    zno_list_chk['Chemistry'] = false;
    zno_list_chk['Biology'] = false;
    zno_list_chk['TK'] = false;

    let zno_list_values = [];

    let high_spec_code = [101, 105, 124, 131, 132, 133, 141, 142, 144, 145, 161, 171, 172, 173, 174, 175, 181, 185, 186, 263, 273, 274];

    let obj_data = null;

    let chkbox_arr = [];

    $.ajaxSetup({
        scriptCharset: "utf-8",
        contentType: "application/json; charset=utf-8"
    });

    $.getJSON(MyAjax.ajaxurl, {action: "my_action"}, function (data) {
        obj_data = data;
        data = data.map(function (item, index)
        {
            item.Code = parseInt(item.Code);
            item.id = parseInt(item.id);
            return item;
        });
        for (let zno in zno_list) {
            zno_list_values[zno] = 0;
            var elem = null;
            var elem1 = null;
            if (zno === 'UkrainianLanguage') {
                elem = $("<input type='checkbox' checked disabled value='" + zno + "'/><label>" + zno_list[zno] + "</label>").appendTo("#chkbox");
                elem1 = $("<input type='text' size='5' id = '" + zno + "' style='width:100%;' placeholder='200'/><br/>").appendTo("#chkbox");
            }
            else {
                elem = $("<input type='checkbox' id='chk_" + zno + "' value='" + zno + "' /><label for='chk_" + zno + "'>" + zno_list[zno] + "</label>").appendTo("#chkbox");
                elem1 = $("<input type='text' size='5' id='" + zno + "' disabled style='width:100%;' placeholder='200'/><br/>").appendTo("#chkbox");
            }
            var f = function () {
                zno_list_chk[$(this).val()] = $(this).prop("checked");
                $(this).next().next()[0].disabled = !$(this).prop("checked");

                let numcheck = 0;
                for (let chk in chkbox_arr) {
                    if (chkbox_arr[chk].checked) {
                        numcheck++;
                    }
                }
                for (let chk in chkbox_arr) {
                    if (chk === 0) continue;
                    if (numcheck >= 3) {
                        chkbox_arr[chk].disabled = !chkbox_arr[chk].checked;
                    }
                    else {
                        chkbox_arr[chk].disabled = false;
                    }
                }

                plotSpec(data);
            };
            var f1 = function () {
                zno_list_values[$(this)[0].id] = parseFloat($(this).val().replace(',', '.'));

                if (zno_list_values[$(this)[0].id] < 100 || zno_list_values[$(this)[0].id] > 200) {
                    $(this).css('background-color', '#feb0b0');
                    zno_list_values[$(this)[0].id] = 0;
                }
                else if ($(this).prop('type') !== 'button')
                    $(this).css('background-color', 'inherit');

                plotSpec(data);
            };


            var f2 = function () {
                zno_list_values[$(this)[0].id] = parseFloat($(this).val().replace(',', '.'));

                if (zno_list_values[$(this)[0].id] < 1.0 || zno_list_values[$(this)[0].id] > 12.0) {
                    $(this).css('background-color', '#feb0b0');
                    zno_list_values[$(this)[0].id] = 0;
                }
                else if ($(this).prop('type') !== 'button')
                    $(this).css('background-color', 'inherit');

                plotSpec(data);
            };

            chkbox_arr.push(elem[0]);
            elem.change(f);
            elem1.change(f1);
        }

        //elem = $("<input type='checkbox' id='chk_certificate' value='Certificate'/><label for='chk_certificate'>Бал атестату за 12-ти бальною шкалою</label>").appendTo("#chkbox");
        //elem1 = $("<input type='text' size='5' id='certificate' disabled style='width:100%;'/><br/>").appendTo("#chkbox");

        $("<input type='checkbox' id='chk_rk' value='rk' checked disabled/><label for='chk_rk'>Регіональний коефіцієнт (1,07)</label><br/>").appendTo("#chkbox");
//        $("<input type='checkbox' id='chk_sk' value='sk'/><label for='chk_sk'>Сільський коефіцієнт (1,02)</label><br/>").appendTo("#chkbox").change(f1);
        $("<input type='checkbox' id='chk_sec' value='sec'/><label for='chk_sec'>Галузевий коефіцієнт* (1,02) </label><br/>").appendTo("#chkbox").change(f1);

        let btn = $("<input type='button' id='ok_btn' value='Розрахувати' style='width:100%;'><br/>").appendTo("#chkbox");

        $("<br/><small>*Галузевий коефіцієнт нараховується лише на певні спеціальності (<a href='http://vstup.kpi.kharkov.ua/admission/admission_rules/' target='_blank'>див. перелік додаток 4</a>) за умови подання заяви з першим або другим пріорітетом</small>").appendTo("#chkbox");

        btn.click(f1);
/*
        elem.change(function () {
            $(this).next().next()[0].disabled = !$(this).prop("checked");
            plotSpec(data);
        });
        elem1.change(f2);
*/
        data.sort(function (a, b) {
            return a.Code - b.Code;
        });

        plotSpec(data);
    });

    function calcMark(data) {
        for (let spec in data) {
            let s;
            let ukr_val = data[spec].UkrainianLanguage * zno_list_values['UkrainianLanguage'];
            for (s in zno_list) {
                var prof = (data[spec][s] < 0) ? s : null;
                if (prof) break;
            }
            var prof_val = Math.abs(data[spec][prof]) * zno_list_values[prof];
            let custom_val = 0.;
            let custom_name = "";
            for (s in zno_list) {
                if (s === 'UkrainianLanguage') continue;
                if (data[spec][s] > 0 && zno_list_chk[s] === true) {
                    if (data[spec][s] * zno_list_values[s] > custom_val)
                    {
                        custom_val = data[spec][s] * zno_list_values[s];
                        custom_name = s;
                    }
                }
            }
            let s1 = ukr_val + prof_val + custom_val;
            let s2 = parseFloat(data[spec].UkrainianLanguage) + Math.abs(data[spec][prof]) + parseFloat(data[spec][custom_name]);
            data[spec].mark =  s1 / s2;

            //console.log(s1,s2,data[spec].mark);

            if ($('#chk_certificate').prop('checked') && parseFloat($('#certificate').val()) >= 1.0 && parseFloat($('#certificate').val()) <= 12.0) {
                let Cert = 10.0 * parseFloat($('#certificate').val().replace(',', '.')) + 80.0;
                Cert = Cert < 100 ? 100 : Cert;
                data[spec].mark += data[spec].Certificate * Cert;
            }


            data[spec].mark *= 1.07;
            data[spec].mark *= $('#chk_sk').prop('checked') ? 1.02 : 1.0;

            if ($('#chk_sec').prop('checked') && high_spec_code.indexOf(data[spec].Code) >= 0)
                data[spec].mark *= 1.02;


            data[spec].mark = Math.round(data[spec].mark * 1000.0) / 1000.0;

            data[spec].mark = data[spec].mark > 200.0 ? 200.0 : data[spec].mark;
        }
        return data;
    }

    function plotSpec(data) {
        data = calcMark(data);

        data.sort(function (a, b) {
            if (a.mark === b.mark)
                if (a.Code === b.Code)
                    return 0; //a.Specialization.localeCompare(b.Specialization);
                else
                    return a.Code - b.Code;
            else
                return -a.mark + b.mark;
        });

        $("#spec").empty();
        $("<h2><strong>Спеціальності</strong>, відповідні до ваших НМТ:</h2>").appendTo("#spec");
        let num = 0;
        for (let spec in data) {
            let cond = true;
            let prof = null;

            let other = "";
            let other_coef = 0.;
            let prof_coef = 0.;

            for (let s in zno_list) {
                prof = (data[spec][s] < 0) ? s : null;
                if (prof) break;
            }
            prof_coef = Math.abs(data[spec][prof]);


            let num_of_match = 0;
            for (let s in zno_list) {
                if (s === 'UkrainianLanguage') continue;

                num_of_match += (zno_list_chk[s] === true && data[spec][s] > 0);

                if (data[spec][s] > 0 && zno_list_chk[s] === true) {
                    other += "<b>" + zno_list[s] + "; </b>";
                    other_coef = parseFloat(data[spec][s]);

                }
                if (data[spec][s] > 0 && zno_list_chk[s] === false)
                    other += zno_list[s] + "; ";

            }

            cond = (num_of_match >= 1) && zno_list_chk[prof];

            if (cond) {
                let faculty = data[spec].faculty[0] + data[spec].faculty.substring(1, data[spec].faculty.length).toLocaleLowerCase();
                let total = data[spec].mark;
                total = total ? total : 0;
                let code = String(data[spec].Code);
                code = code.length === 3 ? code : ("0" + code);
                let ukr_coef = parseFloat(data[spec].UkrainianLanguage);
                $("<a href='" + data[spec].link + "' >" + code + " «" + data[spec].Speciality + "»</a><br/>ННІ «" + faculty + "»<br/>").appendTo("#spec");
                $("<span style='font-size:0.7em;padding-left:2em;'><i>Обов'язковий</i> (" + ukr_coef.toFixed(2) + "):<b>" + zno_list['UkrainianLanguage'] + "</b></span><br/>").appendTo("#spec");
                $("<span style='font-size:0.7em;padding-left:2em;'><i>Профільний</i> (" + prof_coef.toFixed(2) + "): <b>" + zno_list[prof] + "</b></span><br/>").appendTo("#spec");
                $("<span style='font-size:0.7em;padding-left:2em;'><i>На вибір</i> (" + other_coef.toFixed(2) + "): " + other + "</span></br>").appendTo("#spec");
                if (total > 0.)
                    $("<span>Конкурсний бал: " + total.toFixed(3) + "</span></br>").appendTo("#spec");

                $("</br>").appendTo("#spec");
                num++;
            }

        }
        $("<p>Знайдено: " + num + " </p>").appendTo("#spec");
    }

}

if (jQuery("#zno").length)
{
    init_khpi_zno_lookup();
}