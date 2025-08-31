@extends('layouts.app')

@section('title', 'プロフィール設定')

@section('content')
<div class="edit-profile-container">
    <div class="edit-profile-header">
        <h1>プロフィール設定</h1>
        <a href="{{ route('user.profile') }}" class="back-btn">マイページに戻る</a>
    </div>

    <div class="edit-profile-form-container">
        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="edit-profile-form">
            @csrf

            <!-- プロフィール画像 -->
            <div class="form-group">
                <label for="profile_image" class="form-label">プロフィール画像</label>
                <div class="profile-image-upload">
                    <div class="current-image">
                        @if($profile && $profile->profile_image)
                        <img src="{{ Storage::url($profile->profile_image) }}" alt="現在のプロフィール画像" id="preview-image" class="profile-preview">
                        @else
                        <div class="no-image-placeholder" id="preview-image">
                            <i class="icon-camera"></i>
                            <p>画像を選択</p>
                        </div>
                        @endif
                    </div>
                    <div class="image-upload-controls">
                        <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png" class="file-input">
                        <label for="profile_image" class="file-input-btn">画像を選択</label>
                        <p class="file-note">JPEG、PNG形式 (最大2MB)</p>
                    </div>
                </div>
                @error('profile_image')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- ユーザー名 -->
            <div class="form-group">
                <label for="name" class="form-label">ユーザー名</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                    class="form-input @error('name') error @enderror" maxlength="20" required>
                @error('name')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- 郵便番号 -->
            <div class="form-group">
                <label for="postal_code" class="form-label">郵便番号</label>
                <input type="text" id="postal_code" name="postal_code"
                    value="{{ old('postal_code', $profile ? $profile->postal_code : '') }}"
                    class="form-input @error('postal_code') error @enderror"
                    placeholder="123-4567" pattern="\d{3}-\d{4}" required>
                <p class="input-note">ハイフンを含めて入力してください（例：123-4567）</p>
                @error('postal_code')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- 住所 -->
            <div class="form-group">
                <label for="address" class="form-label">住所</label>
                <input type="text" id="address" name="address"
                    value="{{ old('address', $profile ? $profile->address : '') }}"
                    class="form-input @error('address') error @enderror"
                    placeholder="東京都渋谷区千駄ヶ谷1-2-3" required>
                @error('address')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- 建物名 -->
            <div class="form-group">
                <label for="building" class="form-label">建物名</label>
                <input type="text" id="building" name="building"
                    value="{{ old('building', $profile ? $profile->building : '') }}"
                    class="form-input @error('building') error @enderror"
                    placeholder="○○マンション101号室（任意）">
                <p class="input-note">任意項目です</p>
                @error('building')
                <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- 送信ボタン -->
            <div class="form-actions">
                <button type="submit" class="submit-btn">更新する</button>
                <a href="{{ route('user.profile') }}" class="cancel-btn">キャンセル</a>
            </div>
        </form>
    </div>
</div>



<script>
    // プロフィール画像プレビュー機能
    document.getElementById('profile_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('preview-image');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview.tagName === 'IMG') {
                    preview.src = e.target.result;
                } else {
                    // プレースホルダーを画像に置換
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'プロフィール画像プレビュー';
                    img.className = 'profile-preview';
                    img.id = 'preview-image';
                    preview.parentNode.replaceChild(img, preview);
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // 郵便番号フォーマット（自動ハイフン挿入）
    document.getElementById('postal_code').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value.length >= 4) {
            value = value.slice(0, 3) + '-' + value.slice(3, 7);
        }
        e.target.value = value;
    });

    // フォームバリデーション
    document.querySelector('.edit-profile-form').addEventListener('submit', function(e) {
        const postalCode = document.getElementById('postal_code').value;
        const postalCodePattern = /^\d{3}-\d{4}$/;

        if (!postalCodePattern.test(postalCode)) {
            e.preventDefault();
            alert('郵便番号は123-4567の形式で入力してください');
            document.getElementById('postal_code').focus();
            return false;
        }
    });
</script>
@endsection