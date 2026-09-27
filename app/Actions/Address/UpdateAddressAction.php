<?php 
namespace App\Actions\Address;

class UpdateAddressAction {
    
   public function excute($request, $validator)
{
    // يأخذ البيانات بعد التأكد من صحتها عن طريق StoreAddressRequest
    $data = $request->validated();

    // الحصول على المستخدم الحالي الذي أرسل الطلب
    $user = $request->user();

    // التحقق هل هذا أول عنوان للمستخدم
    // إذا كان أول عنوان، سيتم جعله عنوانًا افتراضيًا
    $isFirstAddress = $user->addresses()->count() === 0;

    // إضافة قيمة is_default إلى البيانات
    // إذا اختار المستخدم العنوان كافتراضي أو كان هذا أول عنوان
    // فسيتم جعله عنوانًا افتراضيًا
    $data = array_merge(
        $validator->validated(),
        ['is_default' => $request->boolean('is_default') || $isFirstAddress]
    );

    // أخذ رقم الهاتف وإزالة المسافات الزائدة من بدايته ونهايته
    $phone = trim($data['phone'] ?? '');

    // أخذ كود الدولة وإزالة المسافات الزائدة
    $code = trim($data['country_code'] ?? '');

    // التحقق هل رقم الهاتف يبدأ بكود الدولة
    if ($code !== '' && str_starts_with($phone, $code)) {

        // إذا كان يبدأ بكود الدولة، يتم حذف كود الدولة
        // وتخزين الرقم المحلي فقط
        $data['phone'] = substr($phone, strlen($code));
    }

    // إنشاء العنوان وربطه بالمستخدم الحالي في قاعدة البيانات
    $address = $user->addresses()->create($data);

    // إرجاع العنوان الذي تم إنشاؤه
    return $address;
}
}
