import StlViewer from '@/components/stl-viewer';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/react';  // Add useForm and router here
import { useState } from 'react';

// Define a Project type based on your database schema
interface Project {
    id: number;
    title: string;
    description: string;
    type: string;
    url: string | null;
    images: string[] | File[] | null;
    stl_files: string[] | File[] | null;
    materials: string | null;
    specifications: {
        dimensions?: string;
        weight?: string;
        print_time?: string;
        print_settings?: string;
        [key: string]: any;
    } | null;
    completion_date: string | null;
    featured: boolean;
}

interface Props {
    projects?: Project[];
    project?: Project;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: 'Manage Projects',
        href: '/projects',
    }
];
export default function ProjectsManager({ projects = [], project }: Props) {
    const [isEditing, setIsEditing] = useState(!!project);

    // Replace your formData state with Inertia form
    const { data, setData, post, put, processing, errors } = useForm<Partial<Project>>({
        title: project?.title || '',
        description: project?.description || '',
        type: project?.type || '',
        url: project?.url || '',
        materials: project?.materials || '',
        completion_date: project?.completion_date || '',
        featured: project?.featured || false,
        // Initialize other fields as needed
        images: project?.images || null,
        stl_files: project?.stl_files || null,
        specifications: project?.specifications || null,
    });

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value, type } = e.target as HTMLInputElement;
        const val = type === 'checkbox' ? (e.target as HTMLInputElement).checked : value;
        setData(name as keyof Partial<Project>, val);
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, field: 'images' | 'stl_files') => {
        const files = e.target.files;
        if (!files) return;

        // For multiple file uploads
        const fileArray = Array.from(files);
        setData(field, fileArray);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (project) {
            // Update existing project
            put(route('projects.update', project.id), {
                onSuccess: () => {
                    setIsEditing(false);
                    // Optional: Show success message
                }
            });
        } else {
            // Create new project
            post(route('projects.store'), {
                onSuccess: () => {
                    setIsEditing(false);
                    // Optional: Show success message
                }
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Projects Manager" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header remains the same */}
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-3xl font-bold">Projects Manager</h1>
                    <Link
                        href={route('projects.create')}
                        className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                        Add New Project
                    </Link>
                </div>

                {/* Project Form with error handling */}
                {isEditing && (
                    <form onSubmit={handleSubmit} className="mb-8 p-4 border rounded-lg">
                        <h2 className="text-xl font-semibold mb-4">{project ? 'Edit Project' : 'Create New Project'}</h2>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block mb-1">Title</label>
                                <input
                                    type="text"
                                    name="title"
                                    value={data.title || ''}
                                    onChange={handleInputChange}
                                    className="w-full border rounded px-3 py-2"
                                    required
                                />
                                {errors.title && <div className="text-red-500 text-sm mt-1">{errors.title}</div>}
                            </div>

                            <div>
                                <label className="block mb-1">Type</label>
                                <input
                                    type="text"
                                    name="type"
                                    value={data.type || ''}
                                    onChange={handleInputChange}
                                    className="w-full border rounded px-3 py-2"
                                    required
                                />
                                {errors.type && <div className="text-red-500 text-sm mt-1">{errors.type}</div>}
                            </div>

                            <div className="col-span-2">
                                <label className="block mb-1">Description</label>
                                <textarea
                                    name="description"
                                    value={data.description || ''}
                                    onChange={handleInputChange}
                                    className="w-full border rounded px-3 py-2 h-32"
                                    required
                                />
                                {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
                            </div>

                            <div>
                                <label className="block mb-1">URL (optional)</label>
                                <input
                                    type="text"
                                    name="url"
                                    value={data.url || ''}
                                    onChange={handleInputChange}
                                    className="w-full border rounded px-3 py-2"
                                />
                                {errors.url && <div className="text-red-500 text-sm mt-1">{errors.url}</div>}
                            </div>

                            <div>
                                <label className="block mb-1">Materials (optional)</label>
                                <input
                                    type="text"
                                    name="materials"
                                    value={data.materials || ''}
                                    onChange={handleInputChange}
                                    className="w-full border rounded px-3 py-2"
                                />
                                {errors.materials && <div className="text-red-500 text-sm mt-1">{errors.materials}</div>}
                            </div>

                            <div>
                                <label className="block mb-1">Completion Date (optional)</label>
                                <input
                                    type="date"
                                    name="completion_date"
                                    value={data.completion_date || ''}
                                    onChange={handleInputChange}
                                    className="w-full border rounded px-3 py-2"
                                />
                                {errors.completion_date && <div className="text-red-500 text-sm mt-1">{errors.completion_date}</div>}
                            </div>

                            <div className="flex items-center">
                                <input
                                    type="checkbox"
                                    id="featured"
                                    name="featured"
                                    checked={!!data.featured}
                                    onChange={handleInputChange}
                                    className="mr-2"
                                />
                                <label htmlFor="featured">Featured Project</label>
                                {errors.featured && <div className="text-red-500 text-sm mt-1">{errors.featured}</div>}
                            </div>

                            <div className="col-span-2">
                                <label className="block mb-1">Images (optional)</label>
                                <input
                                    type="file"
                                    name="images"
                                    onChange={(e) => handleFileChange(e, 'images')}
                                    className="w-full border rounded px-3 py-2"
                                    multiple
                                    accept="image/*"
                                />
                                {errors.images && <div className="text-red-500 text-sm mt-1">{errors.images}</div>}
                            </div>

                            <div className="col-span-2">
                                <label className="block mb-1">STL Files (optional)</label>
                                <input
                                    type="file"
                                    name="stl_files"
                                    onChange={(e) => handleFileChange(e, 'stl_files')}
                                    className="w-full border rounded px-3 py-2"
                                    multiple
                                    accept=".stl"
                                />
                                {errors.stl_files && <div className="text-red-500 text-sm mt-1">{errors.stl_files}</div>}
                            </div>
                        </div>

                        <div className="mt-4">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 mr-2 disabled:opacity-50"
                            >
                                {processing ? 'Saving...' : 'Save Project'}
                            </button>
                            <button
                                type="button"
                                onClick={() => setIsEditing(false)}
                                className="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                )}

                {/* Rest of your component remains the same */}
                   {/* Projects List */}
                   <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {projects.map((project) => (
                        <div key={project.id} className="border rounded-lg overflow-hidden">
                            {project.stl_files && project.stl_files.length > 0 && (
                                 <StlViewer
                                 filepath={`/models/${project.stl_files[0]}`}
                                 width={400}
                                 height={300}
                             />
                            )}

                            <div className="p-4">
                                <div className="flex justify-between items-start">
                                    <h3 className="text-xl font-semibold">{project.title}</h3>
                                    {project.featured && (
                                        <span className="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Featured</span>
                                    )}
                                </div>

                                <p className="text-gray-600 mt-2">{project.type}</p>
                                <p className="mt-2 line-clamp-3">{project.description}</p>

                                {project.materials && (
                                    <p className="mt-2 text-sm"><span className="font-semibold">Materials:</span> {project.materials}</p>
                                )}

                                {project.completion_date && (
                                    <p className="mt-1 text-sm"><span className="font-semibold">Completed:</span> {new Date(project.completion_date).toLocaleDateString()}</p>
                                )}

                                <div className="mt-4 flex space-x-2">
                                    <Link
                                        href={`/projects/${project.id}`}
                                        className="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        href={`/projects/${project.id}/edit`}
                                        className="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {projects.length === 0 && (
                    <div className="text-center py-12 bg-gray-50 rounded-lg">
                        <p className="text-gray-500">No projects found. Create your first project!</p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
