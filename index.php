<?php
/*
*
*/
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Shop;
use App\Categorypromotion;
use App\Promotion;
use App\Shopspromotion;
use App\Categoryshop;
use App\Activationspromotion;
use App\Favorite;
use App\View;
use App\Account;
use Carbon\Carbon;  

class OrganizationController extends Controller
{
	
		public $yes_no = [
			'no', 'yes'
		];

		public $passed_no_passed = [
			'no_passed', 'passed'
		];
		
		public $renewal = [
			'every_month', 'unlimited', 'one_time'
		];

    /**
     * add_organization.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_organization()
    { 
		$language = Auth::user()->language;
		
		$this->languages = include('languages/' . $language . '.php');
		
        return view('organization.add_organization', [
			'languages' => $this->languages		
		]);
    }
		 
	 /**
     * croopie logo
     *
     * @return \Illuminate\Http\Response
     */
    public function croppie_logo(Request $request)
    { 	
		
		if($request->isMethod('post')){
			
			$data = $request->image;

			$image_array_1 = explode(";", $data);

			$image_array_2 = explode(",", $image_array_1[1]);

			$data = base64_decode($image_array_2[1]);

			$imageName = $request->filename;

			$path = public_path() . '/image/' . $request->path . '/';

			file_put_contents( $path . $imageName, $data);
			
			return response()->json(array('msg'=> $imageName), 200);
		}
	}
	 
	 /**
     * add shop.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_shop(Request $request)
    { 	
	
		$language = Auth::user()->language;
		
		$this->languages = include('languages/' . $language . '.php');
		
		if($request->isMethod('post')){
			
			if (
				empty($request->description_en) || 
				empty($request->name_en)|| 
				empty($request->description_az) || 
				empty($request->name_az)|| 
				empty($request->description)|| 
				empty($request->name)
			) {
				return response()->json(array('msg'=> 'No_all_languages'), 200);
			}
			
			$filename_logo = null;
			
			if($request->hasFile('logo')) {				
				$filename_logo = $request->hidden_logo;				
			}
			
			$filename = null;
			
			if($request->hasFile('image')) {
				
				$filename = $request->hidden_image;	
			}
			
			if ($request->monday) {
				$monday = [
					'from' => $request->monday_from,
					'to' => $request->monday_to
				];
			} else {
				$monday = null;
			}

			if ($request->tuesday) {
				$tuesday = [
					'from' => $request->tuesday_from,
					'to' => $request->tuesday_to
				];
			} else {
				$tuesday = null;
			}

			if ($request->wednesday) {
				$wednesday = [
					'from' => $request->wednesday_from,
					'to' => $request->wednesday_to
				];
			} else {
				$wednesday = null;
			}
			if ($request->thursday) {
				$thursday = [
					'from' => $request->thursday_from,
					'to' => $request->thursday_to
				];
			} else {
				$thursday = null;
			}

			if ($request->friday) {
				$friday = [
					'from' => $request->friday_from,
					'to' => $request->friday_to
				];
			} else {
				$friday = null;
			}

			if ($request->saturday) {
				$saturday = [
					'from' => $request->saturday_from,
					'to' => $request->saturday_to
				];
			} else {
				$saturday = null;
			}

			if ($request->sunday) {
				$sunday = [
					'from' => $request->sunday_from,
					'to' => $request->sunday_to
				];
			} else {
				$sunday = null;
			}

			$time_work = [
				'monday' => $monday,
				'tuesday' => $tuesday,
				'wednesday' => $wednesday,
				'thursday' => $thursday,
				'friday' => $friday,
				'saturday' => $saturday,
				'sunday' => $sunday
			];
				
			Shop::create([
				'name' => $request->name,
				'description' => $request->description,
				'name_en' => $request->name_en,
				'description_en' => $request->description_en,
				'name_az' => $request->name_az,
				'description_az' => $request->description_az,
				'categorypromotion_id' => $request->category,
				'category_id' => $request->category_id,
				'account_id' => Auth::user()->account_id,
				'address' => $request->address,
				'address_en' => $request->address_en,
				'address_az' => $request->address_az,
				'google_address' => $request->google_address,				
				'color' => $request->color,
				'timework' => serialize($time_work),
				'phone' => $request->phone,
				'logo' => $filename_logo,
				'photo' => $filename,	
				'menu' => $request->menu,
				'lat' => $request->lat,
				'lng' => $request->lng,
				'isVisiable' => (isset($request->isVisiable) ? 1 : 0),
				'email' => $request->email,							
			]);

			return response()->json(array('msg'=> true), 200);
		}
				
		$categoryshop = Categoryshop::where('isVisiable', '=', 1)->get();
				
		if (Auth::user()->account_name->email != 'admin@mail.ru') {
			$shops = Shop::where('account_id', '=', Auth::user()->account_name->id)->paginate(25);
		} else {
			$shops = Shop::paginate(25);
		}

        return view('organization.add_shop', [
			'languages' => $this->languages,
			'shops' => $shops,	
			'categoryshop' => $categoryshop,				
		]);
    }
}