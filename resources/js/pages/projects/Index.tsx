import StlViewer from '@/components/stl-viewer';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { getFileUrl } from '@/utils/storage';

interface Project {
    id: number;
    title: string;
    description: string;
    type: string;
    url: string | null;
    images: string | null; // This is actually a JSON string, not an array
    stl_files: string | null; // This is actually a JSON string, not an array
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
    projects: Project[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
];

export default function Index({ projects = [] }: Props) {
    // Function to safely parse JSON string
    const parseJsonString = (jsonString: string | null): string[] => {
        if (!jsonString) return [];
        try {
            return JSON.parse(jsonString);
        } catch (e) {
            console.error('Error parsing JSON:', e);
            return [];
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Projects" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Projects</h1>
                    <Link href={route('projects.create')} className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Add New Project
                    </Link>
                </div>

                {/* Projects List */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {projects.map((project) => {
                        // Parse JSON strings for each project
                        const stlFiles = parseJsonString(project.stl_files);
                        const images = parseJsonString(project.images);

                        return (
                            <div key={project.id} className="overflow-hidden rounded-lg border">
                                {/* STL Viewer */}
                                {stlFiles.length > 0 ? (
                                    <div className="">
                                        <StlViewer
                                            filepath={getFileUrl(stlFiles[0])}
                                            width="100%"
                                            height="100%"
                                        />
                                    </div>
                                ) : images.length > 0 ? (
                                    // Display first image if no STL file
                                    <img
                                        src={getFileUrl(images[0])}
                                        alt={project.title}
                                        className="h-[300px] w-full object-cover"
                                    />
                                ) : (
                                    // Placeholder if no STL or image
                                    <div className="flex h-[300px] items-center justify-center bg-gray-100">
                                        <span className="text-gray-500">No preview available</span>
                                    </div>
                                )}

                                <div className="p-4">
                                    <div className="flex items-start justify-between">
                                        <h3 className="text-xl font-semibold">{project.title}</h3>
                                        {project.featured && (
                                            <span className="rounded bg-yellow-100 px-2 py-1 text-xs text-yellow-800">Featured</span>
                                        )}
                                    </div>

                                    <p className="mt-2 text-gray-600">{project.type}</p>
                                    <p className="mt-2 line-clamp-3">{project.description}</p>

                                    {project.materials && (
                                        <p className="mt-2 text-sm">
                                            <span className="font-semibold">Materials:</span> {project.materials}
                                        </p>
                                    )}

                                    {project.completion_date && (
                                        <p className="mt-1 text-sm">
                                            <span className="font-semibold">Completed:</span> {new Date(project.completion_date).toLocaleDateString()}
                                        </p>
                                    )}

                                    <div className="mt-4 flex space-x-2">
                                        <Link
                                            href={route('projects.show', project.id)}
                                            className="rounded bg-blue-100 px-3 py-1 text-blue-700 hover:bg-blue-200"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            href={route('projects.edit', project.id)}
                                            className="rounded bg-gray-100 px-3 py-1 text-gray-700 hover:bg-gray-200"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {projects.length === 0 && (
                    <div className="rounded-lg bg-gray-50 py-12 text-center">
                        <p className="text-gray-500">No projects found. Create your first project!</p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
