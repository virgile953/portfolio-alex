import { useEffect, useState } from 'react';

interface Project {
    id?: number;
    title?: string;
    description?: string;
    type?: string;
    url?: string | null;
    images?: string | File[] | null | string[] | any; // Handle both JSON strings and file arrays
    stl_files?: string | File[] | null | string[] | any; // Handle both JSON strings and file arrays
    materials?: string | null;
    specifications?: {
        dimensions?: string;
        weight?: string;
        print_time?: string;
        print_settings?: string;
        [key: string]: any;
    } | null;
    completion_date?: string | null;
    featured?: boolean;
}

interface ProjectFormProps {
    data: Partial<Project>;
    setData: (key: keyof Project, value: any) => void;
    errors: Record<string, string>;
    processing: boolean;
    handleSubmit: (e: React.FormEvent) => void;
    isEditing: boolean;
    imageUrls?: string[];
    stlUrls?: string[];
}

export default function ProjectForm({ data, setData, errors, processing, handleSubmit, isEditing, imageUrls, stlUrls }: ProjectFormProps) {
    // States to track parsed files for display
    const [parsedImages, setParsedImages] = useState<string[]>([]);
    const [parsedStlFiles, setParsedStlFiles] = useState<string[]>([]);

    // Add this utility function to generate S3 URLs
    const getFileUrl = (path: string) => {
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

    // console.log("ProjectForm rendering with data:", data);

    // Parse JSON strings for display if data.images or data.stl_files are strings
    useEffect(() => {
        // console.log("useEffect running for data.images or data.stl_files", data);

        if (data.images) {
            if (typeof data.images === 'string') {
                try {
                    setParsedImages(JSON.parse(data.images));
                } catch (e) {
                    console.error('Error parsing images:', e);
                    setParsedImages([]);
                }
            } else if (Array.isArray(data.images)) {
                setParsedImages(data.images.filter((item) => typeof item === 'string') as string[]);
            }
        } else {
            // If data.images is falsy, ensure parsedImages is empty
            setParsedImages([]);
        }

        if (data.stl_files) {
            if (typeof data.stl_files === 'string') {
                try {
                    setParsedStlFiles(JSON.parse(data.stl_files));
                } catch (e) {
                    console.error('Error parsing STL files:', e);
                    setParsedStlFiles([]);
                }
            } else if (Array.isArray(data.stl_files)) {
                setParsedStlFiles(data.stl_files.filter((item) => typeof item === 'string') as string[]);
            }
        } else {
            // If data.stl_files is falsy, ensure parsedStlFiles is empty
            setParsedStlFiles([]);
        }
    }, [data.images, data.stl_files]);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value, type } = e.target as HTMLInputElement;
        const val = type === 'checkbox' ? (e.target as HTMLInputElement).checked : value;
        // console.log(`Setting ${name} to:`, val);
        setData(name as keyof Project, val);
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, field: 'images' | 'stl_files') => {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        // For multiple file uploads
        const fileArray = Array.from(files);

        // Special handling for STL files
        if (field === 'stl_files') {
            // Validate file extension manually
            const validFiles = fileArray.filter(file => {
                const extension = file.name.split('.').pop()?.toLowerCase();
                if (extension !== 'stl') {
                    console.error(`File ${file.name} is not an STL file`);
                    return false;
                }
                return true;
            });

            if (validFiles.length !== fileArray.length) {
                alert('Some files were not STL files and were ignored.');
            }

            setData(field, validFiles);
        } else {
            // Regular handling for other file types
            setData(field, fileArray);
        }

        // Log the files being set
        console.log(`Setting ${field} to:`, fileArray);
    };

    // Handle removing existing files - FIXED
    const removeFile = (field: 'images' | 'stl_files', index: number) => {
        if (field === 'images') {
            // Create a copy of the current data.images array
            const currentImages = Array.isArray(data.images)
                ? [...data.images]
                : parsedImages.length > 0
                    ? [...parsedImages]
                    : [];

            // Remove the file at the specified index
            currentImages.splice(index, 1);

            // Update both the parent's data and our local state
            setData('images', currentImages.length > 0 ? currentImages : null);
            setParsedImages(currentImages.filter(item => typeof item === 'string') as string[]);

            // console.log("After removal, images:", currentImages);
        } else {
            // Same approach for stl_files
            const currentStlFiles = Array.isArray(data.stl_files)
                ? [...data.stl_files]
                : parsedStlFiles.length > 0
                    ? [...parsedStlFiles]
                    : [];

            currentStlFiles.splice(index, 1);

            setData('stl_files', currentStlFiles.length > 0 ? currentStlFiles : null);
            setParsedStlFiles(currentStlFiles.filter(item => typeof item === 'string') as string[]);

            // console.log("After removal, stl_files:", currentStlFiles);
        }
    };

    // Log before return to check what's actually being rendered
    // console.log("Final render state - data:", data, "parsedImages:", parsedImages, "parsedStlFiles:", parsedStlFiles);

    return (
        <form onSubmit={handleSubmit} className="mb-8 rounded-lg border p-4">
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label className="mb-1 block">Title</label>
                    <input
                        type="text"
                        name="title"
                        value={data.title || ''}
                        onChange={handleInputChange}
                        className="w-full rounded border px-3 py-2"
                    />
                    {errors.title && <div className="mt-1 text-sm text-red-500">{errors.title}</div>}
                </div>

                <div>
                    <label className="mb-1 block">Type</label>
                    <input
                        type="text"
                        name="type"
                        value={data.type || ''}
                        onChange={handleInputChange}
                        className="w-full rounded border px-3 py-2"
                    />
                    {errors.type && <div className="mt-1 text-sm text-red-500">{errors.type}</div>}
                </div>

                <div className="col-span-2">
                    <label className="mb-1 block">Description</label>
                    <textarea
                        name="description"
                        value={data.description || ''}
                        onChange={handleInputChange}
                        className="h-32 w-full rounded border px-3 py-2"
                    />
                    {errors.description && <div className="mt-1 text-sm text-red-500">{errors.description}</div>}
                </div>

                <div>
                    <label className="mb-1 block">URL (optional)</label>
                    <input type="text" name="url" value={data.url || ''} onChange={handleInputChange} className="w-full rounded border px-3 py-2" />
                    {errors.url && <div className="mt-1 text-sm text-red-500">{errors.url}</div>}
                </div>

                <div>
                    <label className="mb-1 block">Materials (optional)</label>
                    <input
                        type="text"
                        name="materials"
                        value={data.materials || ''}
                        onChange={handleInputChange}
                        className="w-full rounded border px-3 py-2"
                    />
                    {errors.materials && <div className="mt-1 text-sm text-red-500">{errors.materials}</div>}
                </div>

                <div>
                    <label className="mb-1 block">Completion Date (optional)</label>
                    <input
                        type="date"
                        name="completion_date"
                        value={data.completion_date || ''}
                        onChange={handleInputChange}
                        className="w-full rounded border px-3 py-2"
                    />
                    {errors.completion_date && <div className="mt-1 text-sm text-red-500">{errors.completion_date}</div>}
                </div>

                <div className="flex items-center">
                    <input type="checkbox" id="featured" name="featured" checked={!!data.featured} onChange={handleInputChange} className="mr-2" />
                    <label htmlFor="featured">Featured Project</label>
                    {errors.featured && <div className="mt-1 text-sm text-red-500">{errors.featured}</div>}
                </div>

                <div className="col-span-2">
                    <label className="mb-1 block">Images (optional)</label>

                    {/* Existing images */}
                    {parsedImages.length > 0 && (
                        <div className="mb-3 grid grid-cols-4 gap-2">
                            {parsedImages.map((image, index) => (
                                <div key={index} className="group relative">
                                    <img
                                        src={getFileUrl(image)}
                                        alt={`Existing image ${index + 1}`}
                                        className="h-24 w-24 rounded border object-cover"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => removeFile('images', index)}
                                        className="absolute top-0 right-0 rounded-full bg-red-500 p-1 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}

                    <input
                        type="file"
                        name="images"
                        onChange={(e) => handleFileChange(e, 'images')}
                        className="w-full rounded border px-3 py-2"
                        multiple
                        accept="image/*"
                    />
                    {errors.images && <div className="mt-1 text-sm text-red-500">{errors.images}</div>}
                </div>

                <div className="col-span-2">
                    <label className="mb-1 block">STL Files (optional)</label>

                    {/* Existing STL files */}
                    {parsedStlFiles.length > 0 && (
                        <div className="mb-3">
                            <p className="mb-1 text-sm font-medium">Current files:</p>
                            <div className="space-y-1">
                                {parsedStlFiles.map((file, index) => (
                                    <div key={index} className="flex items-center justify-between rounded bg-zinc-800 p-2">
                                        <span className="truncate text-sm">{file.split('/').pop()}</span>
                                        <button
                                            type="button"
                                            onClick={() => removeFile('stl_files', index)}
                                            className="text-red-500 hover:text-red-700"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    <input
                        type="file"
                        name="stl_files"
                        onChange={(e) => handleFileChange(e, 'stl_files')}
                        className="w-full rounded border px-3 py-2"
                        multiple
                        accept=".stl"
                    />
                    {errors.stl_files && <div className="mt-1 text-sm text-red-500">{errors.stl_files}</div>}
                </div>
            </div>

            {/* Specifications Section */}
            <div className="col-span-2 mt-6">
                <h3 className="text-lg font-semibold mb-4">Specifications</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="mb-1 block">Dimensions</label>
                        <input
                            type="text"
                            value={data.specifications?.dimensions || ''}
                            onChange={(e) => setData('specifications', {
                                ...data.specifications,
                                dimensions: e.target.value
                            })}
                            className="w-full rounded border px-3 py-2"
                            placeholder="e.g., 200mm x 150mm x 100mm"
                        />
                    </div>

                    <div>
                        <label className="mb-1 block">Weight</label>
                        <input
                            type="text"
                            value={data.specifications?.weight || ''}
                            onChange={(e) => setData('specifications', {
                                ...data.specifications,
                                weight: e.target.value
                            })}
                            className="w-full rounded border px-3 py-2"
                            placeholder="e.g., 250g"
                        />
                    </div>

                    <div>
                        <label className="mb-1 block">Print Time</label>
                        <input
                            type="text"
                            value={data.specifications?.print_time || ''}
                            onChange={(e) => setData('specifications', {
                                ...data.specifications,
                                print_time: e.target.value
                            })}
                            className="w-full rounded border px-3 py-2"
                            placeholder="e.g., 12 hours"
                        />
                    </div>

                    <div>
                        <label className="mb-1 block">Print Settings</label>
                        <input
                            type="text"
                            value={data.specifications?.print_settings || ''}
                            onChange={(e) => setData('specifications', {
                                ...data.specifications,
                                print_settings: e.target.value
                            })}
                            className="w-full rounded border px-3 py-2"
                            placeholder="e.g., 0.2mm layer height, 20% infill"
                        />
                    </div>
                </div>
            </div>

            <div className="mt-6">
                <button
                    type="submit"
                    disabled={processing}
                    className="mr-2 rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                >
                    {processing ? 'Saving...' : isEditing ? 'Update Project' : 'Save Project'}
                </button>
                <a href={route('projects.index')} className="inline-block rounded bg-gray-300 px-4 py-2 hover:bg-gray-400">
                    Cancel
                </a>
            </div>
        </form>
    );
}
