<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Көптілді әдепкі мәндер';
$string['privacy:metadata'] = 'Көптілді әдепкі мәндер плагині жеке деректерді сақтамайды.';

// Capabilities.
$string['mlangdefaults:manage'] = 'Көптілді әдепкі мәндер параметрлерін басқару';
$string['mlangdefaults:overridecourse'] = 'Курстар үшін көптілді әдепкі мәндерді қайта анықтау';
$string['mlangdefaults:viewdiagnostics'] = 'Көптілді әдепкі мәндер диагностикасын көру';

// Settings.
$string['settings'] = 'Параметрлер';
$string['enabled'] = 'Плагинді қосу';
$string['enabled_desc'] = 'Көптілді плейсхолдерлерді автоматты түрде енгізуді қосу';
$string['languages'] = 'Тілдер';
$string['languages_desc'] = 'Тіл кодтарының ретімен үтірмен бөлінген тізімі (мысалы, kk,ru,en)';
$string['fallbacklang'] = 'Әдепкі тіл';
$string['fallbacklang_desc'] = 'Әдепкі ретінде пайдаланылатын тіл коды (әдепкі: ru)';
$string['creationonly'] = 'Тек құру беттері';
$string['creationonly_desc'] = 'Әдепкі мәндерді тек құру беттерінде енгізу, ешқашан өңдеу беттерінде емес';
$string['skipifmlangpresent'] = '{mlang} бар болса өткізіп жіберу';
$string['skipifmlangpresent_desc'] = 'Егер өрісте {mlang} тегтері бар болса, енгізбеу';
$string['showtoast'] = 'Енгізгеннен кейін хабарлама көрсету';
$string['showtoast_desc'] = 'Әдепкі мәндер енгізілгенде хабарлама көрсету';

// Templates.
$string['templates'] = 'Үлгілер';
$string['template_course_fullname'] = 'Курстың толық атауы үлгісі';
$string['template_course_fullname_desc'] = 'Курстың толық атауы өрісі үшін үлгі';
$string['template_course_summary'] = 'Курстың сипаттамасы үлгісі';
$string['template_course_summary_desc'] = 'Курстың сипаттамасы өрісі үшін үлгі';
$string['template_section_name'] = 'Бөлім атауы үлгісі';
$string['template_section_name_desc'] = 'Бөлім атауы өрісі үшін үлгі';
$string['template_section_summary'] = 'Бөлім сипаттамасы үлгісі';
$string['template_section_summary_desc'] = 'Бөлім сипаттамасы өрісі үшін үлгі';
$string['template_activity_name'] = 'Белсенділік атауы үлгісі';
$string['template_activity_name_desc'] = 'Белсенділік атауы өрісі үшін үлгі';
$string['template_activity_intro'] = 'Белсенділік кіріспесі үлгісі';
$string['template_activity_intro_desc'] = 'Белсенділік кіріспесі өрісі үшін үлгі';

// Field mappings.
$string['fieldmappings'] = 'Өріс сәйкестіктері';
$string['fieldmappings_desc'] = 'Қандай беттердегі қандай өрістер көптілді әдепкі мәндерді алуы керектігін баптау';
$string['addmapping'] = 'Пайдаланушы сәйкестігін қосу';
$string['editmapping'] = 'Сәйкестікті өңдеу';
$string['deletemapping'] = 'Сәйкестікті жою';
$string['pagepattern'] = 'Бет үлгісі';
$string['pagepattern_desc'] = 'Бет URL-дерін сәйкестендіру үшін реттік өрнек (мысалы, /course/edit\.php)';
$string['fieldselector'] = 'Өріс селекторы';
$string['fieldselector_desc'] = 'CSS селекторы немесе өріс ID (мысалы, id_fullname)';
$string['fieldtype'] = 'Өріс түрі';
$string['fieldtype_desc'] = 'Өріс түрі: text немесе editor';
$string['templatekey'] = 'Үлгі кілті';
$string['templatekey_desc'] = 'Қолданылатын үлгіні анықтау үшін кілт';
$string['priority'] = 'Басымдық';
$string['priority_desc'] = 'Жоғары басымдықты сәйкестіктер бірінші қолданылады';
$string['enabled_mapping'] = 'Қосылған';
$string['builtin'] = 'Кіріктірілген';
$string['custom'] = 'Пайдаланушы';

// Help strings for form fields.
$string['pagepattern_help'] = 'Бет URL-дерін сәйкестендіру үшін реттік өрнек. Мысалы: /course/edit\.php курс өңдеу бетіне сәйкес келеді. Тікелей нүктені сәйкестендіру үшін \. пайдаланыңыз.';
$string['fieldselector_help'] = 'Нысанаға алу үшін CSS селекторы немесе өріс ID. Мысалы: id_fullname id="fullname" бар өріске сәйкес келеді. Редакторлар үшін редактор ID-ін пайдаланыңыз, мысалы id_summary_editor.';
$string['templatekey_help'] = 'Қолданылатын үлгіні анықтау үшін міндетті емес кілт. Бос болса, жүйе өріс атауы бойынша үлгіні анықтауға тырысады. Мысалдар: course_fullname, course_summary, section_name, activity_name, activity_intro.';
$string['priority_help'] = 'Бірнеше сәйкестіктер бір өріске сәйкес келгенде жоғары басымдықты сәйкестіктер бірінші қолданылады. Әдепкі: 100.';

// Module overrides.
$string['moduleoverrides'] = 'Модуль түрлерін қайта анықтау';
$string['moduleoverrides_desc'] = 'Белсенділік модульдерінің нақты түрлері үшін үлгілерді баптау';
$string['module'] = 'Модуль';
$string['enablemodule'] = 'Осы модуль үшін қосу';
$string['template_module_name'] = 'Атау үлгісі';
$string['template_module_intro'] = 'Кіріспе үлгісі';

// Category overrides.
$string['categoryoverrides'] = 'Категорияларды қайта анықтау';
$string['categoryoverrides_desc'] = 'Нақты категориялардағы курстар үшін үлгілерді баптау';
$string['category'] = 'Категория';
$string['addcategoryoverride'] = 'Категория қайта анықтауын қосу';
$string['deletecategoryoverride'] = 'Категория қайта анықтауын жою';

// Course overrides.
$string['courseoverrides'] = 'Курс қайта анықтаулары';
$string['courseoverrides_desc'] = 'Осы нақты курс үшін үлгілерді қайта анықтау';
$string['usesitedefaults'] = 'Сайт әдепкі мәндерін пайдалану';
$string['disabledincourse'] = 'Осы курста өшіру';
$string['template_course_fullname_override'] = 'Курстың толық атауы үлгісі (қайта анықтау)';
$string['template_course_summary_override'] = 'Курстың сипаттамасы үлгісі (қайта анықтау)';
$string['template_activity_name_override'] = 'Белсенділік атауы үлгісі (қайта анықтау)';
$string['template_activity_intro_override'] = 'Белсенділік кіріспесі үлгісі (қайта анықтау)';

// Diagnostics.
$string['diagnostics'] = 'Диагностика';
$string['filtercheck'] = 'Multilang2 фильтрін тексеру';
$string['filterenabled'] = 'Multilang2 фильтрі қосылған';
$string['filterdisabled'] = 'Multilang2 фильтрі қосылмаған';
$string['filterwarning'] = 'Ескерту: Көптілді плейсхолдерлердің дұрыс жұмыс істеуі үшін Multilang2 фильтрі "Мазмұн және тақырыптар" үшін қосылған болуы керек.';
$string['enablefilter'] = 'Multilang2 фильтрін қосу';
$string['recentinjections'] = 'Соңғы енгізулер';
$string['noinjections'] = 'Енгізулер әлі тіркелмеген';
$string['pagetype'] = 'Бет түрі';
$string['fieldname'] = 'Өріс атауы';
$string['user'] = 'Пайдаланушы';
$string['course'] = 'Курс';
$string['moduletype'] = 'Модуль түрі';
$string['time'] = 'Уақыт';
$string['testinject'] = 'Енгізуді тестілеу';
$string['testinject_desc'] = 'Берілген контекст үшін үлгілердің қалай шешілетінін алдын ала көру';

// Messages.
$string['insertedtemplate'] = 'Көптілді үлгі енгізілді—kk/ru/en өңдеңіз';
$string['mappingsaved'] = 'Сәйкестік сақталды';
$string['mappingdeleted'] = 'Сәйкестік жойылды';
$string['overridesaved'] = 'Қайта анықтау сақталды';

// Help.
$string['help'] = 'Көмек';
$string['authorguide'] = 'Автор нұсқаулығы';
$string['authorguide_desc'] = 'Контент авторлары үшін көптілді плейсхолдерлерді пайдалану бойынша нұсқаулық';

