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

/**
 * Arabic language strings for profilefield_hijridate.
 *
 * @package    profilefield_hijridate
 * @copyright  2026 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'تاريخ هجري';
$string['startyear'] = 'سنة البداية';
$string['startyear_help'] = 'أقدم سنة هجرية يمكن اختيارها في القائمة.';
$string['endyear'] = 'سنة النهاية';
$string['endyear_help'] = 'أحدث سنة هجرية يمكن اختيارها في القائمة.';
$string['startyearafterend'] = 'لا يمكن أن تكون سنة البداية أكبر من سنة النهاية';
$string['conversionmode'] = 'نمط التحويل';
$string['conversionmode_help'] = 'اختر كيفية تحديد التاريخ الهجري: إدخال يدوي من قبل المتدرب، أو تحويل تلقائي من التاريخ الميلادي مع السماح بالتعديل، أو حقل مشتق ومقفل (للقراءة فقط).';
$string['mode_manual'] = 'إدخال يدوي فقط';
$string['mode_autoconvert_editable'] = 'تحويل تلقائي من التاريخ الميلادي (مع السماح بالتعديل اليدوي)';
$string['mode_autoconvert_locked'] = 'تحويل تلقائي وقفل الحقل (للقراءة فقط)';
$string['sourcefield'] = 'حقل التاريخ الميلادي المصدر';
$string['sourcefield_help'] = 'الاسم المختصر لحقل الملف الشخصي للتاريخ الميلادي المطلوب تحويله (مثل: dob أو birthdate).';
$string['displayformat'] = 'صيغة العرض';
$string['displayformat_help'] = 'طريقة عرض التاريخ الهجري في صفحة الملف الشخصي.';
$string['format_iso'] = 'الصيغة القياسية (مثال: 1394-04-22)';
$string['format_text'] = 'الصيغة النصية (مثال: 22 ربيع الآخر 1394 هـ)';
$string['format_both'] = 'الصيغة المزدوجة (مثال: 1394-04-22 (22 ربيع الآخر 1394 هـ))';
$string['defaultdata'] = 'القيمة الافتراضية';
$string['defaultdata_help'] = 'تاريخ هجري افتراضي اختياري بصيغة YYYY-MM-DD.';
$string['day'] = 'اليوم';
$string['month'] = 'الشهر';
$string['year'] = 'السنة';
$string['hijri_suffix'] = 'هـ';
$string['notset'] = 'غير محدد';
$string['err_requireddate'] = 'التاريخ الهجري مطلوب.';
$string['err_incompletedate'] = 'يرجى اختيار تاريخ هجري مكتمل (اليوم والشهر والسنة).';
$string['err_invaliddate'] = 'التاريخ الهجري المدخل غير صحيح.';
$string['err_invaliddefaultdata'] = 'يجب أن تكون القيمة الافتراضية بصيغة YYYY-MM-DD وتمثل تاريخاً هجرياً صحيحاً.';

$string['month1'] = 'محرم';
$string['month2'] = 'صفر';
$string['month3'] = 'ربيع الأول';
$string['month4'] = 'ربيع الآخر';
$string['month5'] = 'جمادى الأولى';
$string['month6'] = 'جمادى الآخرة';
$string['month7'] = 'رجب';
$string['month8'] = 'شعبان';
$string['month9'] = 'رمضان';
$string['month10'] = 'شوال';
$string['month11'] = 'ذو القعدة';
$string['month12'] = 'ذو الحجة';

$string['privacy:metadata:profilefield_hijridate:tableexplanation'] = 'يقوم ملحق حقل التاريخ الهجري بتخزين تواريخ المستخدمين الهجرية في جدول بيانات الحقول المخصصة للمستخدمين.';
$string['privacy:metadata:profilefield_hijridate:userid'] = 'معرّف المستخدم الذي تم حفظ تاريخه الهجري.';
$string['privacy:metadata:profilefield_hijridate:fieldid'] = 'معرّف حقل الملف الشخصي المخصص للتاريخ الهجري.';
$string['privacy:metadata:profilefield_hijridate:data'] = 'التاريخ الهجري للمستخدم بصيغة YYYY-MM-DD.';
$string['privacy:metadata:profilefield_hijridate:dataformat'] = 'صيغة البيانات المخزنة.';
