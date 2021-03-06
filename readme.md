﻿در این ریپازیتوری فایل‌های اصلی پنل و نسخه‌های مختلف ماژول‌ها به همراه تمامی فایل‌های مورد نیاز برای نصب و توسعه مرکز تماس نگه‌داری می‌شود. ساختار این ریپو به شکل زیر می‌باشد که در ادامه هر یک را توضیح می‌دهیم:

## فولدر Installer:
با اجرای اسکریپت nains.php یک پکیج از آخرین نسخه تمام ماژول‌ها و پنل ساخته شده و دستورعمل نصب در ترمینال نمایش داده می‌شود.

---

## فولدر Mobile:
سورس و فایل نصب برنامه‌های موبایل (apk) ساخته شده برای مرکز تماس.

---

## فولدر Modules:
در این فولدر لیست تمامی ماژول‌های ساخته شده مرکزتماس به با دسته‌بندی نوع کاربری آن‌ها موجود است. نام ماژول‌ها به این صورت می‌باشد.

- نام‌ ماژول@نسخه‌ ماژول#حداقل نسخه سازگار پنل
مثال:
queues@4#13.tgz به معنای ماژول queue، نسخه 4 و سازگار با پنل 13 یا بالاتر

- ماژول‌هایی که نامشان با ! شروع شده به معنای ماژول‌های ضروری پنل که برای کارکرد درست سیستم، حتما باید نصب باشند.
مثال:
!cdrreport@7#13.tgz به معنای ماژول cdrreport، یک ماژول ضروری برای پنل، نسخه 7 و سازگار با پنل 13 یا بالاتر

- ماژول‌هایی که نامشان با _ شروع شده به معنای ماژول‌هایی که بنا به دلایلی نیمه‌کاره رها شده‌اند.
مثال:
_peoplereports@7#10.tgz به معنای ماژول peoplereports، نسخه 7 و سازگار با پنل 10 یا بالاتر که دیگر نیازی به نصب در جایی را ندارد.

- ماژول‌هایی که انتهای شماره نسخه آنها، عبارت ing آمده، به معنای ماژول‌هایی که همچنان درحال توسعه می‌باشند و هنوز کامل نیستند.
مثال:
sms@1ing.tgz به معنای ماژول sms، نسخه در حال توسعه 1

---

## سرور مرکز تماس:
یک سرور centos بوده که توسط برنامه asterisk به درگاه‌های تلفن ارتباط برقرار می‌کند و logها و eventها و methodها را در اختیار سرور قرار می‌دهد. تنظیمات asterisk در فایل‌های متنی conf روی لینوکس ذخیره می‌شود و در نهایت وظیفه خواندن و اعمال تغییرات بر عهده asterisk است.

## freepbx چیست؟
وب‌اپلیکیشنی که روی همان سرور نصب شده و وظیفه ‌اصلی‌ش، انتقال تنظیمات از دیتابیس به فایل‌های متنی conf که در پاراگراف قبل توضیح داده شد، می‌باشد. همچنین در این اپلیکیشن، بخش‌هایی برای گزارش‌گیری از logهای ساخته شده و همچنین مدیریت تنظیمات asterisk تعبیه‌ شده.

زبان سرور این اپلیکیشن PHP بوده و دیتابیس مورد استفاده آن، MySql می‌باشد.
بدلیل محدودیت‌های freepbx و انگلیسی بودن و ظاهر نچندان دلچسب آن، شرکت مهنا اقدام به اعمال تغییرات اساسی در آن شده است. از جمله این تغییرات، اضافه کردن NodeJs به برنامه برای اعمال تغییرات بلادرنگ، تغییر تمامی ماژول‌های قبلی و ماژول‌های جدید دیگر که در ساختار قبلی ممکن نبود، می‌باشد. از این به بعد، در این متن به این پنل تغییر یافته، mahnaPbx خواهیم گفت. در ادامه به توضیحات ساختار این برنامه خواهیم پرداخت.

---

برنامه mahnaPbx و ماژول‌های آن در آدرس var/www/html/admin قرار دارد. دیتابیس تنظیمات آن، asterisk روی MySql و جداول logهای تماس، در دیتابیس asteriskcdrdb قرار دارد.

ساختار فولدر admin که در سرور قرار دارد، به شکل زیر است:
- فایل: config.php
تمامی درخواست‌ها به این صفحه فرستاده می‌شود و از طریق این فایل پس از بررسی session و حق دسترسی کاربر و دیگر موارد، به route مورد نظر هدایت می‌شود.
- فایل system.xml:
کاربرد اصلی برای نگه‌داری نسخه کنونی پنل و تغییرات اعمال شده در نسخه‌های مختلف پنل، همچنین برخی تنظیمات خاص مانند title و logo نسخه نمایشی پنل در این فایل نگه‌داری می‌شود.
- فولدر views:
در این فولدر، کامپوننت‌های هدر، فوتر و ... قرار دارد.
- فولدر libraries:
لایبرری‌های php بکار رفته در پنل در این فولدر قرار میگیرد.
- فولدر assets:
در این فولدر لایبرری‌ها و استایل‌ها و پلاگین‌های جاواسکریپت و css قرار می‌گیرد.
- فولدر node:
در این فولدر، اسکریپت اصلی nodejs و ماژول‌های npm آن قرار گرفته است. فایل index.js فایل main بوده و خودش سایر اسکریپت‌های دیگر node را اجرا می‌کند.
- فولدر i18n:
فایل‌های ترجمه ماژول‌ها برای زبان‌های مختلف در این فولدر قرار می‌گیرد. البته بدلیل فارسی بودن پنل و عدم نیاز به زبان‌های دیگر، زبان ماژول‌های جدید ساخته شده همه به طور پیش‌فرض فارسی و به صورت inline پیاده‌سازی شده‌اند.
- فولدر helpers:
فایل‌های helper که عمدتا از زمان خود freepbx باقی مانده، در این فولدر قرار می‌گیرد.
- فولدر modules:
تمامی ماژول‌های سیستم در این فولدر نگه‌داری می‌شود.

---

# نکات:

- ماژول naiGrid نسخه کانفیگ شده و تغییر کرده jqgrid برای اصلاح روابط بین کلاینت و سرور. برای متوجه‌شدن طرز کار این ماژول، می‌توانید به سورس ماژول cdrreport نگاه کنید.

- بعد از تغییر در فایل‌های اصلی پنل، در فایل system.xml توضیحات و نسخه جدید را وارد کنید و یک نسخه از کل فولدرهای پنل بجز modules را در فایل زیپ به نام base@version.zip در فولدر Panel ذخیره کنید. همچنین در فولدر Patches، فایل‌های تغییر کرده از نسخه قبل نسبت به این نسخه را با حفظ ساختار، با نام مشابه فایل‌های قبلی حاظر در این فولدر زخیره کنید.

- پکیج‌های موجود در فولدر node_modules فولدر node در پنل، بدلیل استفاده نشدن از package.json نباید حذف شوند. سعی شود در اولین فرصت نسبت به اصلاح آن و حذف این بند از این فایل توضیحات اقدام شود.
