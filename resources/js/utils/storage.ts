/**
 * Generate a full URL for a file stored in S3/R2
 */
export const getFileUrl = (path: string): string => {
    if (!path) return '';

    // Get base URL from environment - this should match your S3 settings
    const baseUrl = 'https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com';
    const bucket = 'fls-9e568ca0-9700-4e8f-976c-b37c71a870e6';

    // Handle both formats: with or without bucket name
    if (path.startsWith('projects/')) {
        return `${baseUrl}/${bucket}/${path}`;
    }

    return path; // Already a full URL or other format
};
