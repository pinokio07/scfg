<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RefUnloco;
use App\Models\RefAirline;
use App\Models\OrgHeader;
use App\Models\OrgAddress;
use App\Models\OrgContact;
use App\Models\FileEdocs;
use App\Models\FileEdocLogs;
use App\Imports\OrgImport;
use App\Imports\CompanyDataImport;
use App\Exports\OrgExport;
use App\Exports\CompanyDataExport;
use DataTables, Excel, Str, Auth, DB, Arr;

class SetupOrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
          $query = OrgHeader::query();

          if(count($request->all()) > 0){
            $data = $request->all();

            foreach ($data as $key => $value) {
              if($key == 'OH_Code') {
                $query->where(function($c) use ($value){
                  $c->where('OH_Code', 'LIKE', "%$value%")
                    ->orWhere('OH_LegacyCode', 'LIKE', "%$value%");
                });
              } else {
                $query->where($key,'LIKE', '%'.$value.'%');
              }
            }
          }

          return DataTables::eloquent($query)
                            ->addIndexColumn()
                            ->addColumn('OH_Status', function($row){
                              $status = '';
                              if($row->OH_IsActive == true){
                                $status .= '<span class="text-success">Active</span>';
                              } else {
                                $status .= '<span class="text-danger">Not Active</span>';
                              }

                              if($row->OH_IsTempAccount == true){
                                $status .= '; <span class="text-warning">Temporary</span>';
                              }

                              return $status;
                            })
                            ->addColumn('OH_FullName', function($row){
                              $fullname = $row->OH_FullName ?? '';
                              $show = '<a href="/setup/organization/'.$row->id.'/edit">
                                          <span data-toggle="tooltip"
                                             title="'.$fullname.'">'
                                      .Str::limit($fullname, 30)
                                      .'</span></a>';
                              return $show;
                            })
                            ->addColumn('OH_NPWP', function($row){
                              $npwp = $row->taxAddress()->first()->OA_TaxID ?? "-";
                              return $npwp;
                            })
                            ->addColumn('OH_Address1', function($row){
                              $address1 = $row->mainAddress->first()->OA_Address1 ?? '';
                              $show = '<span data-toggle="tooltip"
                                             title="'.$address1.'">'
                                      .Str::limit($address1, 30)
                                      .'</span>';
                              return $show;
                            })
                            ->addColumn('OH_Address2', function($row){
                              $address2 = $row->mainAddress->first()->OA_Address2 ?? '';

                              $show = '<span data-toggle="tooltip"
                                             title="'.$address2.'">'
                                      .Str::limit($address2, 30)
                                      .'</span>';
                              return $show;
                            })
                            ->addColumn('OH_City', function($row){
                              $city = $row->mainAddress->first()->OA_City ?? '';
                              return $city;
                            })
                            ->addColumn('OH_PostCode', function($row){
                              $post = $row->mainAddress->first()->OA_PostCode ?? '';
                              return $post;
                            })
                            ->addColumn('OH_State', function($row){
                              $state = $row->mainAddress->first()->OA_State ?? '';
                              return $state;
                            })
                            ->addColumn('OH_Phone', function($row){
                              $phone = $row->mainAddress->first()->OA_Phone ?? '';
                              return $phone;
                            })
                            ->addColumn('actions', function($row){

                              $btn = '<a href="'.url()->current().'/'.$row->id.'" class="btn btn-xs elevation-2 btn-info"><i class="fas fa-eye"></i> View</a> ';
                              $btn = $btn.'<a href="'.url()->current().'/'.$row->id.'/edit" class="btn btn-xs elevation-2 btn-warning"><i class="fas fa-edit"></i> Edit</a> ';
                              $btn = $btn.'<a data-href="'.url()->current().'/'.$row->id.'" class="btn btn-xs elevation-2 btn-danger delete"><i class="fas fa-trash"></i> Delete</a>';

                              return $btn;
                            })
                            ->rawColumns(['OH_Status', 'OH_LegacyCode', 'actions', 'OH_FullName', 'OH_Address1', 'OH_Address2'])
                            ->toJson();
        }

        $items = collect([
          'id' => 'id',
          'OH_Status' => 'Status',
          'OH_LegacyCode' => 'Legacy Code',
          'OH_Code' => 'Code',
          'OH_FullName' => 'Full Name',
          'OH_NPWP' => 'NPWP',
          'OH_Address1' => 'Address 1',
          'OH_Address2' => 'Address 2',
          'OH_City' => 'City',
          'OH_PostCode' => 'Post Code',
          'OH_State' => 'State',
          'OH_RL_NKClosestPort' => 'UNLOCO',
          'OH_Phone' => 'Phone',
          'actions' => 'actions'
        ]);

        return view('pages.setup.organization.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $organization = new OrgHeader;
        $addressType = $this->getAddressType();

        $ap = false;
        $ar = false;
        $selfBill = false;

        return view('pages.setup.organization.create-edit', compact(['organization', 'ap', 'ar', 'selfBill', 'addressType']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
          'header.OH_FullName' => 'required',
          'address.OA_Address1' => 'required',
        ]);

        if($data){

          $orgHeader = OrgHeader::create($request->header);
          $orgHeader->OH_IsActive = true;
          $orgHeader->save();

          $address = $orgHeader->address()->create($request->address);
          $address->OA_Type = 'OFC';
          $address->OA_CompanyNameOverride = $request->header['OH_FullName'];
          $address->OA_IsActive = true;
          $address->OA_TaxID = Str::replace('_', '', $address->OA_TaxID);
          $address->save();
          
          $exc = ['PT', 'PT.', 'CV', 'CV.'];

          $expName = explode(' ', $request->header['OH_FullName']);

          if(in_array(Str::upper($expName[0]), $exc)){
            unset($expName[0]);
          }

          $name = array_merge($expName);

          // $countcode = substr($request->header['OH_RL_NKClosestPort'], -3);
          $countcode = '';

          $jml = count($name);

          if($jml > 1){
            $namaSet = Str::upper(substr($name[0],0, 3).substr($name[1], 0, 3).$countcode);
          } else {
            $namaSet = Str::upper(substr($name[0],0, 6).$countcode);
          }

          $namaDepan = preg_replace('/[^A-Za-z0-9\-]/', '', $namaSet);

          $cek = OrgHeader::where('OH_Code', 'LIKE', $namaDepan.'%')->get();

          $urut = sprintf('%03d', count($cek) + 1);

          $orgHeader->OH_Code = $namaDepan.$urut;
          $orgHeader->save();

          return redirect('/setup/organization/'.$orgHeader->id.'/edit')->with('sukses', 'Create Organization Success');
        }
    }

    public function createajax(Request $request)
    {
      $data = $request->validate([
        'header.OH_FullName' => 'required',
        'header.OH_Category' => 'required',
        'header.OH_RL_NKClosestPort' => 'required',
        'address.OA_Address1' => 'required',
        'address.OA_City' => 'required',
        'address.OA_PostCode' => 'required',
        'address.OA_RN_NKCountryCode' => 'required',
      ]);

      if($data){

        DB::beginTransaction();

        try {
          $orgHeader = OrgHeader::create($request->header);
          $orgHeader->OH_IsActive = true;
          $orgHeader->save();

          $address = $orgHeader->address()->create($request->address);
          $address->OA_Type = 'OFC';
          $address->OA_CompanyNameOverride = $request->header['OH_FullName'];
          $address->OA_IsActive = true;
          $address->OA_TaxID = Str::replace('_', '', $address->OA_TaxID);
          $address->save();

          $exc = ['PT', 'PT.', 'CV', 'CV.'];

          $expName = explode(' ', $request->header['OH_FullName']);

          if(in_array(Str::upper($expName[0]), $exc)){
            unset($expName[0]);
          }

          $name = array_merge($expName);

          $countcode = substr($request->header['OH_RL_NKClosestPort'], -3);

          $jml = count($name);

          if($jml > 1){
            $namaSet = Str::upper(substr($name[0],0, 3).substr($name[1], 0, 3).$countcode);
          } else {
            $namaSet = Str::upper(substr($name[0],0, 6).$countcode);
          }

          $namaDepan = preg_replace('/[^A-Za-z0-9\-]/', '', $namaSet);

          $cek = OrgHeader::where('OH_Code', 'LIKE', $namaDepan.'%')->get();

          $urut = sprintf('%03d', count($cek) + 1);

          $orgHeader->OH_Code = $namaDepan.$urut;
          $orgHeader->save();

          DB::commit();

          return response()->json(['status' => "OK"]);
        } catch (\Throwable $th) {
          DB::rollback();

          return response()->json([
            'status' => "ERROR",
            'info' => $th->getMessage()
          ]);
        }


      }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(OrgHeader $organization)
    {
        $ap = false;
        $ar = false;
        $selfBill = False;

        $organization->load(['address']);
        $addressType = $this->getAddressType();
        
        return view('pages.setup.organization.create-edit', compact(['organization', 'ap', 'ar', 'selfBill', 'addressType']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(OrgHeader $organization)
    {
        $ap = false;
        $ar = false;
        $selfBill = false;

        $organization->load(['address']);
        $addressType = $this->getAddressType();
        
        return view('pages.setup.organization.create-edit', compact(['organization', 'ap', 'ar', 'selfBill', 'addressType']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrgHeader $organization)
    {
        $data = $request->validate([
          'header.OH_FullName' => 'required',
          'address.OA_Address1' => 'required',
        ]);

        if($data){

          $organization->update($request->header);
          if($organization->mainAddress->isEmpty()){
            $address = $organization->address()
                                 ->create($request->address);
            $address->OA_Type = 'OFC';
            $address->OA_IsActive = true;
            $address->OA_TaxID = Str::replace('_', '', $address->OA_TaxID);
            $address->save();
          } else {
            $mainAddress = $organization->mainAddress->first();
            $mainAddress->update($request->address);
            $mainAddress->OA_TaxID = Str::replace('_', '', $mainAddress->OA_TaxID);
            $mainAddress->save();
          } 

          return redirect('/setup/organization/'.$organization->id.'/edit')->with('sukses', 'Edit Organization Success');
        }
    }

    public function updateapi(Request $request, OrgHeader $organization)
    {
      $organization->update($request->all());

      return "OK";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrgHeader $organization)
    {
        $organization->OH_IsActive = false;
        $organization->save();
        return redirect('/setup/organization')->with('sukses', 'Delete Organization Success.');
    }

    public function select2(Request $request)
    {
        $data = collect([]);

        if($request->has('q') && $request->q != ''){
            $search = $request->q;
            if($request->has('precise') && $request->precise > 0){
              $query = OrgHeader::where(function($q) use ($search){
                                    $q->where('OH_Code','=',"$search")
                                      ->orWhere('OH_FullName','=',"$search");
                                });
            } else {
              $query = OrgHeader::where(function($q) use ($search){
                                    $q->where('OH_Code','LIKE',"%$search%")
                                      ->orWhere('OH_FullName','LIKE',"%$search%")
                                      ->orWhere('OH_LegacyCode', 'LIKE', "%$search%");
                                });
            }

            if($request->has('id') && $request->id != ''){
              $query->where('id', '<>', $request->id);
            }
            if($request->has('type')){
              $query->where($request->type, true);
            }            
            if($request->has('country')){
              $country = $request->country;
              $query->whereHas('address', function($ad) use ($country){
                $ad->where('OA_RN_NKCountryCode', $country);
              });
            }            
            if($request->has('address')){
              $query->join('tps_org_address', function($ja){
                $ja->on('tps_org_header.id', '=', 'tps_org_address.OA_OH')
                   ->where('OA_IsActive', true);
              }, 'left outer')
              ->selectRaw('tps_org_header.*, tps_org_address.*');
            }
            $query->where('OH_IsActive', true)
                  ->where('OH_IsTempAccount', false);

            $data = $query->get();
            
        }

        if($data->isEmpty() && $request->q !== null){

          $data[] = [
            'OH_FullName' => $request->q,
            'OH_LegacyCode' => '',
            'OA_Address1' => '',
            'OA_TaxID' => '',
            'OA_Phone' => '',
          ];
 
         }

        return response()->json($data);
    }

    public function select2address(Request $request)
    {
        $output = '<option selected disabled value="">Select...</option>';
        $result = OrgAddress::where('OA_OH', $request->val)->get();
        foreach ($result as $res) {
          $output .= '<option value="'.$res->id.'" data-detail="'
          .$res->OA_Address1.' '.$res->OA_Address2.' '.$res->OA_City.' '.$res->OA_State.' '.$res->OA_PostCode.' '.$res->country->RN_Desc.'">'
                      .$res->OA_Address1.' '.$res->OA_Address2.' '.$res->OA_City.' '.$res->OA_State.' '.$res->OA_PostCode.' '.$res->country->RN_Desc.'</option>';
        }

        echo $output;
    }

    public function download()
    {
      return Excel::download(new OrgExport(), 'organization.xlsx');
    }

    public function upload(Request $request)
    {
      Excel::import(new OrgImport(), $request->upload);

      return redirect('/setup/organization')->with('sukses', 'Upload Success.');
    }

    public function downloadcompanydata()
    {
      return Excel::download(new CompanyDataExport(), 'company-data.xlsx');
    }

    public function uploadcompanydata(Request $request)
    {
      Excel::import(new CompanyDataImport(), $request->upload);

      return redirect('/setup/organization')->with('sukses', 'Upload Success.');
    }    

    public function select2contacts(Request $request)
    {
        $output = '<option selected disabled value="">Select...</option>';
        $result = OrgContact::where('OC_OH', $request->val)->get();
        foreach ($result as $res) {
          $output .= '<option value="'.$res->id.'"
                              data-category="'.$res->OC_JobCategory.'"
                              data-phone="'.$res->OC_Phone.'"
                              data-mobile="'.$res->OC_Mobile.'"
                              data-homephone="'.$res->OC_HomePhone.'"
                              data-email="'.$res->OC_Email.'"
                              data-addressoverride="'.$res->OC_OH_AddressOverride.'">'
                      .$res->OC_ContactName.'</option>';
        }

        echo $output;
    }

    public function ajaxaddress(OrgHeader $organization)
    {
      $organization->load('address');
      $addressType = $this->getAddressType();

      return DataTables::of($organization->address)
                        ->addIndexColumn()
                        ->addColumn('type', function($row) use ($addressType){
                          $type = $row->OA_Type;
                          return $addressType[$type] ?? "-";
                        })
                        ->addColumn('status', function($row){
                          $checkbox = '<input type="checkbox" class="form-check-input aktif" value="1" data-id="'.$row->id.'" ';
                          if($row->OA_IsActive == true){
                            $checkbox .= 'checked';
                          }
                          $checkbox .= '>';
                          return $checkbox;
                        })
                        ->addColumn('action', function($row){
                          $btn = '<a href="#" class="btn btn-xs elevation-2 btn-warning mr-1 editAddress"
                                  data-toggle="modal"
                                  data-target="#modal-address"
                                  data-id="'.$row->id.'"
                                  data-duplicate="0"
                                  data-type="'.$row->OA_Type.'"
                                  data-address1="'.$row->OA_Address1.'"
                                  data-address2="'.$row->OA_Address2.'"
                                  data-additional="'.$row->OA_AdditionalAddressInformation.'"
                                  data-company="'.$row->OA_CompanyNameOverride.'"
                                  data-country="'.$row->OA_RN_NKCountryCode.'"
                                  data-city="'.$row->OA_City.'"
                                  data-state="'.$row->OA_State.'"
                                  data-postcode="'.$row->OA_PostCode.'"
                                  data-relatedport="'.$row->OA_RL_NKRelatedPortCode.'"
                                  data-phone="'.$row->OA_Phone.'"
                                  data-mobile="'.$row->OA_Mobile.'"
                                  data-fax="'.$row->OA_Fax.'"
                                  data-email="'.$row->OA_Email.'"
                                  data-language="'.$row->OA_Language.'"
                                  ><i class="fas fa-trash"></i> Edit</a>';
                          $btn .= '<a href="#" class="btn btn-xs elevation-2 btn-info mr-1 copyAddress"
                                  data-toggle="modal"
                                  data-target="#modal-address"
                                  data-id="'.$row->id.'"
                                  data-duplicate="1"
                                  data-type="'.$row->OA_Type.'"
                                  data-address1="'.$row->OA_Address1.'"
                                  data-address2="'.$row->OA_Address2.'"
                                  data-additional="'.$row->OA_AdditionalAddressInformation.'"
                                  data-company="'.$row->OA_CompanyNameOverride.'"
                                  data-country="'.$row->OA_RN_NKCountryCode.'"
                                  data-city="'.$row->OA_City.'"
                                  data-state="'.$row->OA_State.'"
                                  data-postcode="'.$row->OA_PostCode.'"
                                  data-relatedport="'.$row->OA_RL_NKRelatedPortCode.'"
                                  data-phone="'.$row->OA_Phone.'"
                                  data-mobile="'.$row->OA_Mobile.'"
                                  data-fax="'.$row->OA_Fax.'"
                                  data-email="'.$row->OA_Email.'"
                                  data-language="'.$row->OA_Language.'"
                                  ><i class="fas fa-copy"></i> Copy</a>';

                          $btn .= '<a href="#" class="btn btn-xs elevation-2 btn-danger hapusAddress" data-id="'.$row->id.'"><i class="fas fa-trash"></i> Delete</a>';

                          return $btn;
                        })
                        ->rawColumns(['status', 'action'])
                        ->make(true);
    }

    public function storeaddress(Request $request)
    {
      $organization = OrgHeader::findOrFail($request->organization_id);
      $oaCode = $organization->OH_Code
                .$request->OA_RN_NKCountryCode
                .$request->OA_RL_NKRelatedPortCode
                .$request->OA_PostCode;
      $request->request->add([
        'OA_Code' => $oaCode
      ]);
      $address = $organization->address()->create($request->except('organization_id'));
      $address->OA_CompanyNameOverride = $organization->OH_FullName;
      $address->OA_IsActive = true;
      $address->save();
    }

    public function updateaddress(Request $request, OrgAddress $address)
    {
      $organization = $address->header;
      if($request->duplicate == '1'){
        $newAddress = $address->replicate()
                              ->fill($request->except(['organization_id', 'duplicate']));
        $newAddress->OA_CompanyNameOverride = $organization->OH_FullName;
        $newAddress->save();
      } else {
        $address->update($request->except(['organization_id', 'duplicate']));
        if($address->OA_CompanyNameOverride == ''){
          $address->OA_CompanyNameOverride = $organization->OH_FullName;
          $address->save();
        }        
      }
    }

    public function changestate(Request $request)
    {
      $address = OrgAddress::findOrFail($request->id);
      $address->OA_IsActive = $request->val;
      $address->save();
    }

    public function destroyaddress(Request $request, OrgAddress $address)
    {
       $address->delete();
    }

    public function ajaxcontact(OrgHeader $organization)
    {
      $organization->load('contacts');
      return DataTables::of($organization->contacts)
                       ->addIndexColumn()
                       ->addColumn('status', function($row){
                         $checkbox = '<input type="checkbox" class="form-check-input statecontact" value="1" data-id="'.$row->id.'" ';
                         if($row->OC_IsActive == true){
                           $checkbox .= 'checked';
                         }
                         $checkbox .= '>';
                         return $checkbox;
                       })
                       ->addColumn('action', function($row){

                        $btn = '<a href="#" class="btn btn-xs elevation-2 btn-warning mr-1 editContact"
                                data-toggle="modal"
                                data-target="#modal-contact"
                                data-id="'.$row->id.'"
                                data-name="'.$row->OC_ContactName.'"
                                data-title="'.$row->OC_Salutation.'"
                                data-category="'.$row->OC_JobCategory.'"
                                data-email="'.$row->OC_Email.'"
                                data-language="'.$row->OC_Language.'"
                                data-workphone="'.$row->OC_Phone.'"
                                data-extension="'.$row->OC_PhoneExtension.'"
                                data-mobile="'.$row->OC_Mobile.'"
                                data-home="'.$row->OC_HomePhone.'"
                                data-workaddress="'.$row->OC_OH_AddressOverride.'"
                                ><i class="fas fa-edit"></i> Edit</a>';

                        $btn .= '<a href="#" class="btn btn-xs elevation-2 btn-danger hapusContact" data-id="'.$row->id.'"><i class="fas fa-trash"></i> Delete</a>';

                        return $btn;
                      })
                      ->rawColumns(['status', 'action'])
                       ->make(true);
    }

    public function storecontact(Request $request)
    {
      $organization = OrgHeader::findOrFail($request->organization_id);
      $contact = $organization->contacts()->create($request->except('organization_id'));
      $contact->OC_IsActive = true;
      $contact->save();
    }

    public function updatecontact(OrgContact $contact, Request $request)
    {
      $contact->update($request->except(['organization_id']));
    }

    public function changecontactstate(Request $request)
    {
      $contact = OrgContact::findOrFail($request->id);
      $contact->OC_IsActive = $request->val;
      $contact->save();
    }

    public function destroycontact(Request $request, OrgContact $contact)
    {
       $contact->delete();
    }
    
    public function getRestricted()
    {
      $data = [
        'php',
        'html',
        'exe',
        'bat',
        'vba',
        'js',
        'xml',
      ];
      return $data;
    }

    public function getAddressType()
    {
        $data = collect([
          'OFC' => 'Office Main',
          'AWB' => 'Air Way Bill',
          'DLV' => 'Delivery',
          'PUP' => 'Pick Up',
          'PDV' => 'Pick Up and Delivery',
          'TAX' => 'Tax Invoice',
          'INV' => 'Invoice'
        ]);

        return $data;
    }    

    public function users(Request $request)
    {
      $data = [];
      if($request->has('q') && $request->q != ''){
        $search = $request->q;
        $data = User::where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('email', 'LIKE', '%'.$search.'%')
                    ->limit(5)
                    ->get();
      }
      return response()->json($data);
    }
    
}
