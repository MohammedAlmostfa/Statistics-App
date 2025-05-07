<?php

namespace Database\Seeders;

use App\Models\Receipt;
use App\Models\ReceiptProduct;
use App\Models\Installment;
use App\Models\InstallmentPayment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// إذا كنت تستخدم إصدار Laravel 8+ و trait HasFaker متاح ومثبت، يمكنك استخدامه بدلاً من إنشاء Faker يدوياً
// use Illuminate\Database\Eloquent\Factories\HasFaker;


class ReceiptsTableSeeder extends Seeder
{
    // إذا كنت تستخدم HasFaker: use HasFaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // *** إنشاء مثيل Faker يدوياً هنا إذا لم تستخدم HasFaker ***
        $faker = \Faker\Factory::create();


        // تعطيل قيود المفاتيح الأجنبية مؤقتاً
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // حذف البيانات بترتيب عكسي
        InstallmentPayment::query()->delete();
        Installment::query()->delete();
        ReceiptProduct::query()->delete();
        Receipt::query()->delete();

        // إعادة تفعيل قيود المفاتيح الأجنبية
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- الافتراضات: المستخدمون والعملاء والمنتجات موجودون بالفعل ---
        $userCount = User::count();
        $customerCount = Customer::count();
        $productCount = Product::count();

        if ($userCount === 0 || $customerCount === 0 || $productCount === 0) {
            $this->command->error('Skipping ReceiptsTableSeeder: Ensure you have users, customers, and products seeded.');
            return;
        }

        $this->command->info('Creating 100 Receipts...');

        // إنشاء 100 فاتورة أساسية باستخدام Factory
        // Factory سيقوم الآن بتعيين total_price عشوائياً ونوع (type) عشوائي (نقدي أو أقساط)
        $receipts = Receipt::factory()
                    ->count(100)
                    ->create();

        $this->command->info('100 Receipts created. Adding products, installments, and payments...');

        // التكرار على كل فاتورة تم إنشاؤها لإضافة التفاصيل المرتبطة
        // قم بتمرير $faker إلى الـ closure باستخدام use إذا كنت تستخدم Faker يدوياً
        $receipts->each(function (Receipt $receipt) use ($productCount, $faker) {

            // إنشاء عدد عشوائي من عناصر المنتجات لكل فاتورة (1 إلى 5 منتجات)
            // استخدم $faker هنا
            $numberOfProducts = $faker->numberBetween(1, 5);


            ReceiptProduct::factory()
                ->count($numberOfProducts)
                ->create(['receipt_id' => $receipt->id]) // ربط عناصر المنتجات بالفاتورة الحالية
                // قم بتمرير $faker إلى الـ closure الداخلية أيضاً باستخدام use
                ->each(function (ReceiptProduct $receiptProduct) use ($receipt, $productCount, $faker) {

                    // --- حساب وتحديث أسعار الشراء والبيع لكل ReceiptProduct ---
                    $product = Product::find($receiptProduct->product_id);

                    if (!$product) {
                        $this->command->warn("Product ID {$receiptProduct->product_id} not found for ReceiptProduct ID {$receiptProduct->id}. Skipping price calculation.");
                        return;
                    }

                    // حساب سعر البيع بناءً على نوع الفاتورة (نقدي أو أقساط)
                    // $receipt->type هنا ستعيد القيمة النصية ('نقدي' أو 'اقساط') بفضل الـ Accessor في موديل Receipt
                    $sellingPrice = $product->getSellingPriceForReceiptType($receipt->type);
                    $buyingPrice = $product->getCalculatedBuyingPrice();

                    // تحديث أسعار الشراء والبيع في سجل ReceiptProduct
                    $receiptProduct->update([
                        'selling_price' => $sellingPrice,
                        'buying_price' => $buyingPrice,
                    ]);

                    // --- إنشاء بيانات الأقساط إذا كانت الفاتورة من نوع 'اقساط' ---
                    // *** قم بتصحيح الشرط هنا للمقارنة مع النص 'اقساط' ***
                    if ($receipt->type === 'اقساط') { // قارن مع النص 'اقساط'
                        // إنشاء سجل Installment واحد لهذا الـ ReceiptProduct
                        $installment = Installment::factory()->create([
                            'receipt_product_id' => $receiptProduct->id, // ربط القسط بعنصر المنتج في الفاتورة
                        ]);

                        // --- حساب وتحديث قيم الدفعة الأولى والقسط في سجل Installment ---
                        $itemTotal = $receiptProduct->quantity * $receiptProduct->selling_price;

                        // قيمة الدفعة الأولى والقسط الدوري يمكن أن تكون عشوائية أيضاً لغرض الـ seeding السريع
                        // استخدم $faker هنا
                        $firstPay = $faker->numberBetween(1, max(1, $itemTotal)); // يمكن أن تصل حتى الإجمالي
                        $installmentAmount = $faker->numberBetween(1, max(1, $itemTotal - $firstPay + 1)); // قيمة قسط عشوائية

                        // تحديث سجل Installment بالقيم المحسوبة (أو العشوائية هنا)
                        $installment->update([
                            'first_pay' => $firstPay,
                            'installment' => $installmentAmount,
                           //  pay_cont و installment_type تم تعيينهما بواسطة InstallmentFactory بشكل عشوائي
                        ]);


                        // --- إنشاء دفعات أقساط (Installment Payments) لهذا القسط ---
                        // إنشاء عدد عشوائي من الدفعات بوضعيات مختلفة
                        // استخدم $faker هنا أيضاً
                        $numberOfSamplePayments = $faker->numberBetween(1, 5); // إنشاء ما بين 1 و 5 دفعات تجريبية
                        $lastPaymentDate = Carbon::parse($receipt->receipt_date); // تاريخ بدء الدفعات اللاحقة

                        for ($i = 0; $i < $numberOfSamplePayments; $i++) {
                            // تحديد الفاصل الزمني بناءً على نوع القسط (القيمة العددية المخزنة)
                            $interval = 1;
                            $dateUnit = 'day';
                            // جلب نوع القسط الفعلي من سجل القسط الذي تم إنشاؤه
                            $installmentTypeNumeric = $installment->getRawOriginal('installment_type'); // احصل على القيمة العددية المخزنة فعلياً

                            switch($installmentTypeNumeric) { // استخدم القيمة العددية للمقارنة
                                case array_search('يومي', Installment::TYPE_MAP): $dateUnit = 'day';
                                    break;
                                case array_search('اسبوعي', Installment::TYPE_MAP): $dateUnit = 'week';
                                    break;
                                case array_search('شهري', Installment::TYPE_MAP): $dateUnit = 'month';
                                    break;
                                default: $dateUnit = 'day';
                                    break;
                            }

                            // حساب تاريخ الدفعة التالي باستخدام Carbon
                            $nextPaymentDate = Carbon::parse($lastPaymentDate)->add($interval, $dateUnit);

                            // تحديد حالة الدفعة (مثلاً: بعضها مدفوع، بعضها متأخر بشكل عشوائي)
                            $statuses = array_keys(InstallmentPayment::TYPE_MAP);
                            // استخدم $faker هنا
                            $status = $faker->randomElement($statuses);

                            // إنشاء سجل دفعة القسط
                            InstallmentPayment::factory()->create([
                                'installment_id' => $installment->id, // ربط الدفعة بالقسط
                                'payment_date' => $nextPaymentDate,
                                'amount' => $faker->numberBetween(10, max(10, $installmentAmount * 2)), // مبلغ دفعة عشوائي (يمكن أن يكون حول قيمة القسط المحسوبة)
                                'status' => $status, // تخزين القيمة العددية للحالة
                            ]);

                            $lastPaymentDate = $nextPaymentDate; // تحديث تاريخ آخر دفعة
                        }
                    }
                });

            // *** إزالة منطق حساب وتحديث الإجمالي الكلي للفاتورة هنا ***
            // $receipt->refresh();
            // $actualTotal = $receipt->receiptProducts->sum(function ($receiptProduct) { ... });
            // $receipt->update(['total_price' => $actualTotal]);
        });

        $this->command->info('✅ تم بنجاح زرع (Seeding) 100 فاتورة مع كافة التفاصيل المرتبطة.');
    }
}
