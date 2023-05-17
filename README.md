# Intelligent curtains

# Unlink the git from folder

Issuing

git remote rm origin
will delete the config settings from .git/config.

Then issue

rm .git/FETCH_HEAD
to get rid of the FETCH_HEAD which still points to github.

# Link the git from folder

To register a project as a local Git repository the first thing you need to do is perform the following command at your project root:

git init
This will create a .git folder at your project root and will allow you to start using Git in that repository.

If you want to "push" your local Git repository to a remote Git server (in your case, to GitLab), you'll need to perform the following command first:

git remote add origin <Repository_Location>
You can call origin whatever you like, really, but origin is the standard name for Git remote repositories. <Repository_Location> is the URL to your remote repository. For example, if I had a new project called MyNewProject that I wanted to push to GitLab, I'd perform:

git remote add origin https://gitlab.com/Harmelodic/MyNewProject.git
You can then "push" your changes from your local machine to your remote repo using the following command:

git push origin <branch_name>
where branch name is the name of the branch you want to push, e.g. master.

You can find a good beginners guide to Git here.