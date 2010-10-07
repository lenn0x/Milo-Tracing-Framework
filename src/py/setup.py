from setuptools import setup, find_packages

version = '0.1'

setup(name='milo',
      version=version,
      description="Distributed tracing framework",
      long_description="""\
Milo is designed to remove uncertainty and provide insight into how applications are performing. It is a tracing 
infrastructure with goals of providing low overhead, and application-level transparency. Milo collects data in 
real-time from applications using adaptive sampling, and provides a central location to analyze performance data.
""",
      author='Chris Goffinet',
      author_email='cg@chrisgoffinet.com',
      url='http://github.com/lenn0x/Milo-Tracing-Framework',
      license='Apache',
      packages=find_packages(exclude=['ez_setup', 'examples', 'tests']),
      zip_safe=True,
      )

