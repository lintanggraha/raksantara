<div class="mb-3">
    <label class="form-label required">Vendor Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $vendor->name ?? '') }}" required>
    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Description / Services Provided</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $vendor->description ?? '') }}</textarea>
    @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Contact Name</label>
        <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $vendor->contact_name ?? '') }}">
        @error('contact_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Contact Email</label>
        <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $vendor->contact_email ?? '') }}">
        @error('contact_email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label required">Criticality Level</label>
        <select name="criticality_level" class="form-select" required>
            <option value="">Select Level...</option>
            @foreach(['Low', 'Medium', 'High'] as $level)
                <option value="{{ $level }}" {{ old('criticality_level', $vendor->criticality_level ?? '') === $level ? 'selected' : '' }}>
                    {{ $level }}
                </option>
            @endforeach
        </select>
        @error('criticality_level') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label required">Status</label>
        <select name="status" class="form-select" required>
            <option value="Active" {{ old('status', $vendor->status ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ old('status', $vendor->status ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>
</div>
