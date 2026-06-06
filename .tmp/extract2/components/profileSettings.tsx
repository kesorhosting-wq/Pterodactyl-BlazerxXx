import React, { useState } from 'react';
import http from '@/api/http';
import ContentBox from '@/components/elements/ContentBox';

interface ProfilePictureUploadProps {
    currentProfilePicture?: string;
}

const profileSettings: React.FC<ProfilePictureUploadProps> = ({ currentProfilePicture }) => {
    const [profilePictureUrl, setProfilePictureUrl] = useState<string>(currentProfilePicture || '');
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const [successMessage, setSuccessMessage] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [uploadMode, setUploadMode] = useState<'file' | 'url'>('file');

    // Fetch the CSRF token from the meta tag in the HTML document
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Handle URL change
    const handleUrlChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setProfilePictureUrl(e.target.value);
    };

    // Handle file selection
    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            // Validate file type
            const allowedTypes = ['image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                setUploadError('Please select a valid image file (SVG, ICO, PNG, or JPEG).');
                return;
            }

            // Validate file size (max 2MB)
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                setUploadError('File size must be less than 2MB.');
                return;
            }

            setSelectedFile(file);
            setUploadError(null);
        }
    };

    // Handle form submission
    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (uploadMode === 'url') {
            if (!profilePictureUrl || !isValidUrl(profilePictureUrl)) {
                setUploadError('Please enter a valid URL.');
                return;
            }
        } else {
            if (!selectedFile) {
                setUploadError('Please select a file to upload.');
                return;
            }
        }

        try {
            setLoading(true);
            setUploadError(null);
            setSuccessMessage(null);

            if (uploadMode === 'file') {
                // Handle file upload
                const formData = new FormData();
                formData.append('profile_picture', selectedFile as File);

                const response = await http.post(
                    '/extensions/euphoriatheme/upload-profile-picture-file',
                    formData,
                    {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken ?? '',
                            'Content-Type': 'multipart/form-data',
                        },
                    }
                );

                if (response.data.success) {
                    setSuccessMessage('Profile picture uploaded successfully! The changes will be visible after a page refresh.');
                    // Add cache busting timestamp to force image reload
                    const newUrl = response.data.profile_picture_url + '?t=' + Date.now();
                    setProfilePictureUrl(newUrl);
                    setSelectedFile(null);
                    
                    // Clear the file input
                    const fileInput = document.getElementById('profile_picture_file') as HTMLInputElement;
                    if (fileInput) {
                        fileInput.value = '';
                    }
                } else {
                    setUploadError(response.data.message || 'Failed to upload the profile picture.');
                }
            } else {
                // Handle URL submission
                const response = await http.post(
                    '/extensions/euphoriatheme/upload-profile-picture-url',
                    {
                        profile_picture_url: profilePictureUrl,
                    },
                    {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken ?? '',
                        },
                    }
                );

                if (response.data.success) {
                    setSuccessMessage('Profile picture updated successfully! Please refresh the site to see the updated profile picture.');
                    setProfilePictureUrl(profilePictureUrl);
                } else {
                    setUploadError('Failed to update the profile picture.');
                }
            }
        } catch (error: any) {
            const errorMessage = error.response?.data?.message || 'An error occurred while updating the profile picture.';
            setUploadError(errorMessage);
        } finally {
            setLoading(false);
        }
    };

    // Handle setting default profile picture
    const handleSetDefault = async () => {
        try {
            setLoading(true);
            setUploadError(null);
            setSuccessMessage(null);

            // Send request to the backend to clear the profile picture URL
            const response = await http.post(
                '/extensions/euphoriatheme/reset-profile-picture-url',
                {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken ?? '',
                    },
                }
            );

            if (response.data.success) {
                setSuccessMessage('Profile picture reset to default. Please refresh the site to see the updated profile picture.');
                // Update the profile picture URL in the state to an empty string or default URL
                setProfilePictureUrl('');
            } else {
                setUploadError('Failed to reset the profile picture.');
            }
        } catch (error) {
            setUploadError('An error occurred while resetting the profile picture.');
        } finally {
            setLoading(false);
        }
    };

    // Validate URL
    const isValidUrl = (url: string) => {
        try {
            new URL(url);
            return true;
        } catch (_) {
            return false;
        }
    };

    return (
        <ContentBox title={'Profile Picture'} className={"profile-picture"}>
            <div className="profile-picture-upload-container">
                {/* Upload Mode Toggle */}
                <div className="upload-mode-toggle" style={{ marginBottom: '2rem' }}>
                    <div style={{ display: 'flex', gap: '1rem', marginBottom: '1rem' }}>
                        <button
                            type="button"
                            className={`style-module_4LBM1DKx ${uploadMode === 'file' ? 'style-module_3kBDV_wo' : ''}`}
                            onClick={() => setUploadMode('file')}
                            style={{ 
                                backgroundColor: uploadMode === 'file' ? 'var(--primary-color)' : 'transparent',
                                border: '1px solid var(--primary-color)'
                            }}
                        >
                            Upload File
                        </button>
                        <button
                            type="button"
                            className={`style-module_4LBM1DKx ${uploadMode === 'url' ? 'style-module_3kBDV_wo' : ''}`}
                            onClick={() => setUploadMode('url')}
                            style={{ 
                                backgroundColor: uploadMode === 'url' ? 'var(--primary-color)' : 'transparent',
                                border: '1px solid var(--primary-color)'
                            }}
                        >
                            Enter URL
                        </button>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    {uploadMode === 'file' ? (
                        <div className="form-group" style={{ marginBottom: '2rem' }}>
                            <label htmlFor="profile_picture_file">Select Profile Picture:</label>
                            <input
                                type="file"
                                className="jqTCDz"
                                id="profile_picture_file"
                                accept="image/svg+xml,image/x-icon,image/vnd.microsoft.icon,image/png,image/jpeg"
                                onChange={handleFileChange}
                                required
                                style={{ marginTop: '0.5rem' }}
                            />
                            <div style={{ fontSize: '0.8rem', color: '#666', marginTop: '0.25rem' }}>
                                Accepted formats: SVG, ICO, PNG, JPEG. Max size: 2MB
                            </div>
                            {selectedFile && (
                                <div style={{ marginTop: '0.5rem', color: 'var(--primary-color)' }}>
                                    Selected: {selectedFile.name} ({(selectedFile.size / 1024 / 1024).toFixed(2)}MB)
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="form-group" style={{ marginBottom: '2rem' }}>
                            <label htmlFor="profile_picture_url">Enter Profile Picture URL:</label>
                            <input
                                type="text"
                                className="jqTCDz"
                                id="profile_picture_url"
                                value={profilePictureUrl}
                                onChange={handleUrlChange}
                                placeholder="https://example.com/image.png"
                                required
                                style={{ marginTop: '0.5rem' }}
                            />
                        </div>
                    )}

                    <button type="submit" className="style-module_4LBM1DKx style-module_3kBDV_wo" disabled={loading}>
                        {loading ? (uploadMode === 'file' ? 'Uploading...' : 'Updating...') : (uploadMode === 'file' ? 'Upload' : 'Update')}
                    </button>

                    <button
                        type="button"
                        className="style-module_4LBM1DKx style-module_3kBDV_wo"
                        onClick={handleSetDefault}
                        disabled={loading}
                        style={{ marginLeft: '1rem' }}
                    >
                        {loading ? 'Resetting...' : 'Set as Default'}
                    </button>
                </form>

                {uploadError && <div className="alert alert-danger" style={{ marginTop: '1rem', padding: '0.75rem', backgroundColor: '#fee', border: '1px solid #fcc', borderRadius: '4px', color: '#c33' }}>{uploadError}</div>}
                {successMessage && <div className="alert alert-success" style={{ marginTop: '1rem', padding: '0.75rem', backgroundColor: '#efe', border: '1px solid #cfc', borderRadius: '4px', color: '#3c3' }}>{successMessage}</div>}

                {/* Display the current profile picture */}
                {profilePictureUrl && (
                    <div className="current-profile-picture" style={{ marginTop: '2rem' }}>
                        <h4>Current Profile Picture:</h4>
                        <img src={profilePictureUrl} alt="Profile" style={{ width: '120px', height: '120px', borderRadius: '50%', objectFit: 'cover', border: '3px solid var(--primary-color)' }} />
                    </div>
                )}
            </div>
        </ContentBox>
    );
};

export default profileSettings;