<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoices_details;
use App\Models\Invoices_attatchment;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailSend;

class InvoiceController extends Controller
{

    public function index()
    {

        $shows=Invoice::all();
       return view('invoices.showinvoices',compact('shows'));
    }


    public function create()
    {
        // هاخد الاقسامةبردوعايا عشان هحتاجها
        $sections = Section::all();
        $products = Product::all();
        return view('invoices.add_invoices',compact('sections','products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $store =new Invoice();
        $store->invoice_number=$request->invoice_number;
        $store->invoice_date=$request->invoice_Date;
        $store->Due_date=$request->Due_date;
        $store->product=$request->product;
        $store->section_id=$request->section;
        $store->Amount_collection=$request->Amount_collection;
        $store->Amount_Commission=$request->Amount_Commission;
        $store->Discount=$request->Discount;
        $store->Value_VAT=$request->Value_VAT;
        $store->Rate_VAT=$request->Rate_VAT;
        $store->Total=$request->Total;
        $store->Status="غير مدفوعة";
        $store->Value_Status=0;
        $store->note=$request->note;
        $store->Payment_Date=now();
        $store->save();

        //دي معناها اخر حاجة حجصلت هاتلي الاي دي بتاعها
       $invoices_id = Invoice::latest()->first()->id;

       $invoicesDetails=new Invoices_details();

       $invoicesDetails->invoice_number=$request->invoice_number;
       $invoicesDetails->id_Invoice= $invoices_id;
       $invoicesDetails->product=$request->product;
       $invoicesDetails->Section=$request->section;
       $invoicesDetails->Status="غير مدفوعة";
       $invoicesDetails->Value_Status=0;
       $invoicesDetails->note=$request->note;
       $invoicesDetails->Payment_Date=now();
       $invoicesDetails->user=(Auth::user()->name);
       $invoicesDetails->save();


       // دا مهم جداا  دي بتاعة تخزين الملفات في الداتا بيز والسيرفر
       if($request->hasFile('pic')){
       $invoices_id = Invoice::latest()->first()->id;
       $image = $request->file('pic');
       $file_name =$image->getClientOriginalName();//دي هىتجيب اسم الملف
       $invoice_number=$request->invoice_number; // عملت الخطوة دي عشان هحتاجها تحت

       $invoicesAttatch=new Invoices_attatchment();
       $invoicesAttatch->file_name=$file_name;
       $invoicesAttatch->invoice_id= $invoices_id;
       $invoicesAttatch->invoice_number= $invoice_number;
       $invoicesAttatch->Created_by=(Auth::user()->name);
       $invoicesAttatch->save();
       // هخلي بقا الملف يتحقظ ع السيرفر في المشروع في البابليك يعنيى
       $imageName=$request->pic->getClientOriginalName();
       $request->pic->move(public_path('Attatchments/'.$invoice_number),$imageName);
      }

      // جزئية الايميل هستخ\م اليوزر المفتوح اصلا وفاتح المةوقع
     
      // هضمن معابا الاي دي بتاع الفغانتورة عشان في الايميل هيكون فيه بوتون عرض الفاتورة 
     //Mail::to("momennoh123aa@gmail.com")->send(new emailsend($invoices_id));
     Mail::to("momennoh123aa@gmail.com")->send(new EmailSend(
        'Absence Notification'));
      session()->flash('success','تم اضافة العنصر بنجاح');
      return redirect()->back();
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sections = Section::all();
        $products = Product::all();
        $status = Invoice::findOrFail($id);// من غير جيت 
        return view ('invoices.status_invoice',compact('status','sections','products'));
     
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoices = Invoice::findOrFail($id);
        $sections = Section::all();
        $products = Product::all();
        return view('invoices.edit_invoices',compact('sections','products','invoices'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        $upd =Invoice::findOrFail($request->invoice_id);
        $upd->invoice_number=$request->invoice_number;
        $upd->invoice_date=$request->invoice_Date;
        $upd->Due_date=$request->Due_date;
        $upd->product=$request->product;
        $upd->section_id=$request->section;
        $upd->Amount_collection=$request->Amount_collection;
        $upd->Amount_Commission=$request->Amount_Commission;
        $upd->Discount=$request->Discount;
        $upd->Value_VAT=$request->Value_VAT;
        $upd->Rate_VAT=$request->Rate_VAT;
        $upd->Total=$request->Total;
        $upd->Status="غير مدفوعة";
        $upd->Value_Status=0;
        $upd->note=$request->note;
        $upd->Payment_Date=now();
        $upd->save();

    
      
      session()->flash('update','تم تعديل العنصر بنجاح');
      return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */

     // ممكت اقسم ع اتنين فانكشن عادي شغال بردو 
    public function destroy(Request $request)
    {
        // اكتلر استفادة من السوفت ديليليت اني هعمل ارشيف في الحدات الي اتمسحت وممكن الرجعاها 
 
        $id=$request->invoice_id;
         $invoiceId = Invoice::where('id',$id)->first();
         
         // دلوقتي هعمل انا بقا هنا حاجة عشانةافغرق بين الارشفة الحذف 
             $arsh_id=$request->arsh_id;
        if(!$arsh_id==2)//يعني مش ارشفة
        {
           // دلوقتي لازم لمك اعفل حذف نهائي يتمسح من البابليك بردو فهعمل الخطوه دي 
            $attatch = Invoices_attatchment::where('invoice_id',$id)->first();
            if(!empty($attatch->invoice_number))
            {
            //  Storage::disk('public_uploads')->delete($attatch->invoice_number.'/'.$attatch->file_name);
            Storage::disk('public_uploads')->deleteDirectory($attatch->invoice_number); // دي بتحذف الملف الي فه الصور خالص 
            }

            
            $invoiceId ->forceDelete();  // كد هيمسح نهائي  

            // هعمل نوتفيكشن للحجات دي من نوع تاني 
            session()->flash('delete');
            return redirect()->back();
        }
        else 
        {
            $invoiceId ->Delete();  // كدا هيستعمل السوفت ديليلت 
            session()->flash('arshive');
            return redirect()->back();
        }
         
    }
 

    // دا عشان اعمل حوار لم اختار قسم يظهرلي منتجاتة هوا بس
    public function getproducts($id)
    {
        $products = DB::table("products")->where("section_id", $id)->pluck("product_name", "id");

        // سجل المنتجات للتأكد من وجودها
        \Log::info($products);

        // تحقق مما إذا كانت هناك منتجات للعرض
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }

        return response()->json($products);
    }


 
    //  هنا لازم تعخلي فيه الاي دي والريكوستس عشان هستقبل بيانات بردو زي مدوعة الي حدد بيها 
    public function status_update($id , Request $request)
    {
     // دلوقتي هعمل بقا لو الريكوست الي جاي من الحقل بيساوي كذا اعملي ابديت في الداتا بيز خلي الاستيتيوس كذا واعملي اضافة تاني في جدول الانفويس ديتالز لي بقا عشان يبقا في معلومات الفاتورت الحالتين الدفع امتي وكانت مش مدفوعة امتي وهكذا 
      // ممكن اعمل بطريقتي عادي بس للتنوع بس 
      $upd=Invoice::findOrFail($id);
        if($request->Status ==='مدفوعة')
        {
            $upd->update([
               'Value_Status'=>1 , 
               'Status'=>$request->Status , 
               'Payment_Date'=>$request->Payment_Date , 
            ]);

            invoices_Details::create([
                'id_Invoice' => $id  ,
                'invoice_number' => $request->invoice_number,
                'Section' => $request->section,
                'product' => $request->product,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }

        else {
            $upd->update([
                'Value_Status' => 2,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            invoices_Details::create([
                'id_Invoice' => $id  ,
                'invoice_number' => $request->invoice_number,
                'Section' => $request->section,
                'product' => $request->product,
                'Status' => $request->Status,
                'Value_Status' => 2,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');
    }




   public function paid_invoices()
   {
    $shows=Invoice::where('Value_Status',1)->get();
    return view('invoices.paied_invoices',compact('shows'));
   }
   public function Partially_paid_invoices()
   {
    $shows=Invoice::where('Value_Status',2)->get();
    return view('invoices.Partially_paid_invoices',compact('shows'));
   }
   public function unpaid_invoices()
   {
    $shows=Invoice::where('Value_Status',0)->get();
    return view('invoices.unpaid_invoices',compact('shows'));
   }


   public function Print_invoice($id)
   {
    $shows = Invoice::findOrFail($id);
    $sections = Section::all();
    $products = Product::all();
   return view('invoices.Print_invoice',compact('sections','products','shows'));
   }
   
   public function export()
   {

       return Excel::download(new Invoice, 'invoices.xlsx');

   }


}
