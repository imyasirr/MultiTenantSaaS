<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    // List all companies of authenticated user
    public function index()
    {
        try {
            $companies = auth('api')->user()->companies;
            return response()->json([
                'message' => 'Companies retrieved successfully',
                'companies' => $companies
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve companies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Create new company
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company = auth('api')->user()->companies()->create($request->only('name', 'address', 'industry'));

            // Set as active company if none is set
            if (!auth('api')->user()->active_company_id) {
                auth('api')->user()->update(['active_company_id' => $company->id]);
            }

            return response()->json([
                'message' => 'Company created successfully',
                'company' => $company
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show specific company
    public function show($id)
    {
        try {
            $company = Company::where('id', $id)
                ->where('user_id', auth('api')->id())
                ->firstOrFail();

            return response()->json([
                'message' => 'Company retrieved successfully',
                'company' => $company
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Company not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update company
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company = Company::where('id', $id)
                ->where('user_id', auth('api')->id())
                ->firstOrFail();

            $company->update($request->only('name', 'address', 'industry'));

            return response()->json([
                'message' => 'Company updated successfully',
                'company' => $company
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Company not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete company
    public function destroy($id)
    {
        try {
            $company = Company::where('id', $id)
                ->where('user_id', auth('api')->id())
                ->firstOrFail();

            $company->delete();

            if (auth('api')->user()->active_company_id == $id) {
                auth('api')->user()->update(['active_company_id' => null]);
            }

            return response()->json([
                'message' => 'Company deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Company not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Set active company
    public function setActive($id)
    {
        try {
            $company = Company::where('id', $id)
                ->where('user_id', auth('api')->id())
                ->firstOrFail();

            auth('api')->user()->update(['active_company_id' => $id]);

            return response()->json([
                'message' => 'Active company set successfully',
                'company' => $company
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Company not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to set active company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get current active company
    public function current()
    {
        try {
            $company = auth('api')->user()->activeCompany;

            if (!$company) {
                return response()->json(['message' => 'No active company set'], 404);
            }

            return response()->json([
                'message' => 'Active company retrieved successfully',
                'company' => $company
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve active company',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
