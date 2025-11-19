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

$string['pluginname'] = 'Многоязычные значения по умолчанию';
$string['privacy:metadata'] = 'Плагин многоязычных значений по умолчанию не хранит личных данных.';

// Capabilities.
$string['mlangdefaults:manage'] = 'Управление настройками многоязычных значений по умолчанию';
$string['mlangdefaults:overridecourse'] = 'Переопределение многоязычных значений по умолчанию для курсов';
$string['mlangdefaults:viewdiagnostics'] = 'Просмотр диагностики многоязычных значений по умолчанию';

// Settings.
$string['settings'] = 'Настройки';
$string['enabled'] = 'Включить плагин';
$string['enabled_desc'] = 'Включить автоматическую вставку многоязычных плейсхолдеров';
$string['languages'] = 'Языки';
$string['languages_desc'] = 'Список кодов языков через запятую по порядку (например, kk,ru,en)';
$string['fallbacklang'] = 'Язык по умолчанию';
$string['fallbacklang_desc'] = 'Код языка для использования по умолчанию (по умолчанию: ru)';
$string['creationonly'] = 'Только страницы создания';
$string['creationonly_desc'] = 'Вставлять значения по умолчанию только на страницах создания, никогда на страницах редактирования';
$string['skipifmlangpresent'] = 'Пропускать, если {mlang} уже присутствует';
$string['skipifmlangpresent_desc'] = 'Не вставлять, если поле уже содержит теги {mlang}';
$string['showtoast'] = 'Показывать уведомление после вставки';
$string['showtoast_desc'] = 'Отображать уведомление, когда значения по умолчанию вставлены';

// Templates.
$string['templates'] = 'Шаблоны';
$string['template_course_fullname'] = 'Шаблон полного названия курса';
$string['template_course_fullname_desc'] = 'Шаблон для поля полного названия курса';
$string['template_course_summary'] = 'Шаблон описания курса';
$string['template_course_summary_desc'] = 'Шаблон для поля описания курса';
$string['template_section_name'] = 'Шаблон названия раздела';
$string['template_section_name_desc'] = 'Шаблон для поля названия раздела';
$string['template_section_summary'] = 'Шаблон описания раздела';
$string['template_section_summary_desc'] = 'Шаблон для поля описания раздела';
$string['template_activity_name'] = 'Шаблон названия активности';
$string['template_activity_name_desc'] = 'Шаблон для поля названия активности';
$string['template_activity_intro'] = 'Шаблон введения активности';
$string['template_activity_intro_desc'] = 'Шаблон для поля введения активности';

// Field mappings.
$string['fieldmappings'] = 'Сопоставления полей';
$string['fieldmappings_desc'] = 'Настройка того, какие поля на каких страницах должны получать многоязычные значения по умолчанию';
$string['addmapping'] = 'Добавить пользовательское сопоставление';
$string['editmapping'] = 'Редактировать сопоставление';
$string['deletemapping'] = 'Удалить сопоставление';
$string['pagepattern'] = 'Шаблон страницы';
$string['pagepattern_desc'] = 'Регулярное выражение для сопоставления URL страниц (например, /course/edit\.php)';
$string['fieldselector'] = 'Селектор поля';
$string['fieldselector_desc'] = 'CSS селектор или ID поля (например, id_fullname)';
$string['fieldtype'] = 'Тип поля';
$string['fieldtype_desc'] = 'Тип поля: text или editor';
$string['templatekey'] = 'Ключ шаблона';
$string['templatekey_desc'] = 'Ключ для идентификации используемого шаблона';
$string['priority'] = 'Приоритет';
$string['priority_desc'] = 'Сопоставления с более высоким приоритетом применяются первыми';
$string['enabled_mapping'] = 'Включено';
$string['builtin'] = 'Встроенное';
$string['custom'] = 'Пользовательское';

// Help strings for form fields.
$string['pagepattern_help'] = 'Регулярное выражение для сопоставления URL страниц. Например: /course/edit\.php соответствует странице редактирования курса. Используйте \. для сопоставления буквальной точки.';
$string['fieldselector_help'] = 'CSS селектор или ID поля для нацеливания. Например: id_fullname соответствует полю с id="fullname". Для редакторов используйте ID редактора, например id_summary_editor.';
$string['templatekey_help'] = 'Необязательный ключ для идентификации используемого шаблона. Если пусто, система попытается определить шаблон по имени поля. Примеры: course_fullname, course_summary, section_name, activity_name, activity_intro.';
$string['priority_help'] = 'Сопоставления с более высоким приоритетом применяются первыми, когда несколько сопоставлений соответствуют одному полю. По умолчанию 100.';

// Module overrides.
$string['moduleoverrides'] = 'Переопределения типов модулей';
$string['moduleoverrides_desc'] = 'Настройка шаблонов для конкретных типов модулей активности';
$string['module'] = 'Модуль';
$string['enablemodule'] = 'Включить для этого модуля';
$string['template_module_name'] = 'Шаблон названия';
$string['template_module_intro'] = 'Шаблон введения';

// Category overrides.
$string['categoryoverrides'] = 'Переопределения категорий';
$string['categoryoverrides_desc'] = 'Настройка шаблонов для курсов в определенных категориях';
$string['category'] = 'Категория';
$string['addcategoryoverride'] = 'Добавить переопределение категории';
$string['deletecategoryoverride'] = 'Удалить переопределение категории';

// Course overrides.
$string['courseoverrides'] = 'Переопределения курса';
$string['courseoverrides_desc'] = 'Переопределение шаблонов для этого конкретного курса';
$string['usesitedefaults'] = 'Использовать значения сайта по умолчанию';
$string['disabledincourse'] = 'Отключить в этом курсе';
$string['template_course_fullname_override'] = 'Шаблон полного названия курса (переопределение)';
$string['template_course_summary_override'] = 'Шаблон описания курса (переопределение)';
$string['template_activity_name_override'] = 'Шаблон названия активности (переопределение)';
$string['template_activity_intro_override'] = 'Шаблон введения активности (переопределение)';

// Diagnostics.
$string['diagnostics'] = 'Диагностика';
$string['filtercheck'] = 'Проверка фильтра Multilang2';
$string['filterenabled'] = 'Фильтр Multilang2 включен';
$string['filterdisabled'] = 'Фильтр Multilang2 НЕ включен';
$string['filterwarning'] = 'Предупреждение: Фильтр Multilang2 должен быть включен для "Контента и заголовков" для правильной работы многоязычных плейсхолдеров.';
$string['enablefilter'] = 'Включить фильтр Multilang2';
$string['recentinjections'] = 'Последние вставки';
$string['noinjections'] = 'Вставки еще не зарегистрированы';
$string['pagetype'] = 'Тип страницы';
$string['fieldname'] = 'Имя поля';
$string['user'] = 'Пользователь';
$string['course'] = 'Курс';
$string['moduletype'] = 'Тип модуля';
$string['time'] = 'Время';
$string['testinject'] = 'Тестовая вставка';
$string['testinject_desc'] = 'Предварительный просмотр того, как шаблоны будут разрешены для данного контекста';

// Messages.
$string['insertedtemplate'] = 'Вставлен многоязычный шаблон—отредактируйте kk/ru/en';
$string['mappingsaved'] = 'Сопоставление сохранено';
$string['mappingdeleted'] = 'Сопоставление удалено';
$string['overridesaved'] = 'Переопределение сохранено';

// Help.
$string['help'] = 'Помощь';
$string['authorguide'] = 'Руководство автора';
$string['authorguide_desc'] = 'Руководство для авторов контента по использованию многоязычных плейсхолдеров';

