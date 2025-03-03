/**
 * Generate a full URL for a file stored in cloud storage
 */
export const getFileUrl = (path: string): string => {
    if (!path) return '';

    // Use the proxy route instead of direct R2 URLs
    if (path.startsWith('projects/')) {
        return `/file/${encodeURIComponent(path)}`;
    }

    return path; // Already a full URL or other format
};
